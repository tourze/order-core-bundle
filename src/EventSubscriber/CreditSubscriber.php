<?php

namespace OrderCoreBundle\EventSubscriber;

use CreditBundle\Entity\Account;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Event\AfterOrderCancelEvent;
use OrderCoreBundle\Event\AfterOrderCreatedEvent;
use OrderCoreBundle\Event\BeforeOrderCreatedEvent;
use OrderCoreBundle\Event\BeforePriceRefundEvent;
use OrderCoreBundle\Exception\CreditRefundUserNotFoundException;
use OrderCoreBundle\Service\OrderService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Tourze\CurrencyManageBundle\Entity\Currency;
use Tourze\CurrencyManageBundle\Service\CurrencyService;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;

#[WithMonologChannel(channel: 'order_core')]
class CreditSubscriber
{
    public function __construct(
        private LoggerInterface $logger,
        private OrderService $orderService,
        private ?CurrencyService $currencyService,
        private ?AccountService $accountService,
        private ?TransactionService $transactionService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 如果订单包含了积分的支付信息，那么我们要支付积分
     * 要注意，我们要在订单创建之前就扣除积分
     */
    #[AsEventListener]
    public function checkCreditEnough(BeforeOrderCreatedEvent $event): void
    {
        $checkList = $this->buildCreditCheckList($event->getContract());
        $this->validateCreditSufficiency($event->getContract(), $checkList);
    }

    /**
     * @return array<string, float>
     */
    private function buildCreditCheckList(Contract $contract): array
    {
        $checkList = [];

        foreach ($contract->getPrices() as $price) {
            if (!$this->shouldCheckPrice($price)) {
                continue;
            }

            if ($this->isZeroPrice($price)) {
                $price->setPaid(true);
                continue;
            }

            $checkList = $this->addToCheckList($checkList, $price);
        }

        return $checkList;
    }

    private function shouldCheckPrice(OrderPrice $price): bool
    {
        if (true === $price->isPaid()) {
            return false;
        }

        if ('CNY' === $price->getCurrency()) {
            // TODO 人民币有时候可以使用积分抵扣
            return false;
        }

        if ($price->getMoney() < 0) {
            return false;
        }

        return true;
    }

    private function isZeroPrice(OrderPrice $price): bool
    {
        return '0' === $price->getMoney();
    }

    /**
     * @param array<string, float> $checkList
     * @return array<string, float>
     */
    private function addToCheckList(array $checkList, OrderPrice $price): array
    {
        $currency = $price->getCurrency();

        if (!isset($checkList[$currency])) {
            $checkList[$currency] = 0;
        }

        $money = (float) $price->getMoney();
        $tax = (float) $price->getTax();
        $checkList[$currency] += $money + $tax;

        return $checkList;
    }

    /**
     * @param array<string, float> $checkList
     */
    private function validateCreditSufficiency(Contract $contract, array $checkList): void
    {
        foreach ($checkList as $currency => $checkValue) {
            if (null === $this->currencyService) {
                continue;
            }
            $point = $this->currencyService->getCurrencyByCode($currency);
            if (!$this->isCurrencyValid($point, $currency, $checkValue)) {
                continue;
            }

            if (null === $this->accountService || null === $point) {
                continue;
            }

            $this->checkAccountBalance($contract, $point, $checkValue);
        }
    }

    private function isCurrencyValid(?Currency $point, string $currency, float $checkValue): bool
    {
        if (null === $point) {
            $this->logger->warning("检查积分时找不到指定积分类型[{$currency}]", [
                'price' => $checkValue,
            ]);

            return false;
        }

        return true;
    }

    private function checkAccountBalance(Contract $contract, Currency $point, float $checkValue): void
    {
        if (null === $this->accountService) {
            return;
        }

        $user = $contract->getUser();
        if (null === $user) {
            return;
        }

        $account = $this->accountService->getAccountByUser($user, $point);

        try {
            $currentValue = $this->accountService->getValidAmount($account);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('查询积分服务异常，请稍后重试', 0, $exception);
        }

        if ($currentValue < $checkValue) {
            $this->logger->warning('检查时发现积分不足', [
                'currentValue' => $currentValue,
                'checkValue' => $checkValue,
                'currency' => $point,
                'account' => $account,
            ]);
            throw new \RuntimeException('积分不足');
        }
    }

    /**
     * 支付成功后扣积分
     *
     * @see https://learnku.com/articles/72323
     */
    #[AsEventListener]
    public function payCreditPoint(AfterOrderCreatedEvent $event): void
    {
        foreach ($event->getContract()->getPrices() as $price) {
            if (!$this->shouldProcessPrice($price)) {
                continue;
            }

            $this->processPointPayment($price, $event);
        }

        $this->entityManager->flush();
    }

    private function shouldProcessPrice(OrderPrice $price): bool
    {
        if (true === $price->isPaid()) {
            return false;
        }

        if ('CNY' === $price->getCurrency()) {
            return false;
        }

        if ($price->getMoney() < 0) {
            return false;
        }

        return true;
    }

    private function processPointPayment(OrderPrice $price, AfterOrderCreatedEvent $event): void
    {
        if (null === $this->currencyService) {
            return;
        }

        $point = $this->currencyService->getCurrencyByCode($price->getCurrency());
        if (null === $point) {
            $this->logger->warning("订单扣除积分时发现找不到指定积分[{$price->getCurrency()}]", [
                'price' => $price,
            ]);

            return;
        }

        if (null === $this->accountService || null === $this->transactionService) {
            return;
        }

        $user = $event->getContract()->getUser();
        if (null === $user) {
            return;
        }

        $outAccount = $this->accountService->getAccountByUser($user, $point);
        $this->executePointTransfer($price, $event, $point, $outAccount);
    }

    private function executePointTransfer(
        OrderPrice $price,
        AfterOrderCreatedEvent $event,
        Currency $point,
        Account $outAccount,
    ): void {
        if (null === $this->transactionService || null === $this->accountService) {
            return;
        }

        try {
            $pointCode = $point->getCode() ?? '';
            $result = $this->transactionService->transfer(
                $outAccount,
                $this->accountService->getAccountByName("system-{$point->getName()}-{$pointCode}", $pointCode),
                (float) $price->getMoney(),
                $event->getContract()->getSn(),
                ['price' => $price],
            );
        } catch (\Throwable $exception) {
            $price->setPaid(false);
            $event->setRollback(true);
            $event->stopPropagation();
            $this->logger->error('使用积分支付时发生错误', [
                'event' => $event,
                'exception' => $exception,
            ]);
            throw new \RuntimeException($exception->getMessage(), 0, $exception);
        }

        $this->markPriceAsPaid($price);
    }

    private function markPriceAsPaid(OrderPrice $price): void
    {
        $price->setPaid(true);
        $price->setCanRefund(true);
    }

    /**
     * 单个OrderPrice的积分退还逻辑，要注意这里仅仅处理虚拟积分
     */
    #[AsEventListener]
    public function onCreditPriceRefund(BeforePriceRefundEvent $event): void
    {
        $price = $event->getPrice();

        if ('CNY' === $price->getCurrency()) {
            // 人民币退款，需要找其他接口处理
            return;
        }

        if (true === $price->isRefund()) {
            // 不要重复退
            return;
        }

        if (true !== $price->isCanRefund()) {
            $this->logger->warning('当前积分不允许退款，不退', [
                'price' => $price,
            ]);

            return;
        }

        if (true !== $price->isPaid()) {
            $this->logger->warning('积分数据未支付，不退', [
                'price' => $price,
            ]);

            return;
        }

        if (null === $this->currencyService) {
            return;
        }
        $point = $this->currencyService->getCurrencyByCode($price->getCurrency());
        if (null === $point) {
            $this->logger->warning("订单退还积分时发现找不到指定积分[{$price->getCurrency()}]", [
                'price' => $price,
            ]);

            return;
        }

        if (null === $event->getContract()->getUser()) {
            $this->logger->error('订单退还积分时发现用户不存在', [
                'contract' => $event->getContract(),
            ]);
        }

        $remarkEnv = $_ENV['ORDER_PRINCE_REFUND_REMARK'] ?? null;
        $remark = is_string($remarkEnv) ? $remarkEnv : "{$event->getContract()->getSn()}-{$price->getId()}";

        if (null === $this->transactionService || null === $this->accountService) {
            return;
        }

        try {
            $this->transactionService->transfer(
                // 从系统账号转出
                $this->accountService->getSystemAccount($point),
                // 转入到消费者账户
                $this->accountService->getAccountByUser($event->getContract()->getUser() ?? throw new CreditRefundUserNotFoundException('积分退款时用户不存在'), $point),
                (float) ($price->getMoney() ?? 0),
                $remark,
                [
                    'event' => $event,
                ],
            );
        } catch (\Throwable $exception) {
            $this->logger->error('订单退还积分时发生异常', [
                'exception' => $exception,
                'price' => $price,
            ]);

            return;
        }

        // 转账成功的话，上面的 OrderPrice 记录，我们需要标记为已支付
        $price->setRefund(true);
        $this->entityManager->persist($price);
        $this->entityManager->flush();
    }

    /**
     * 取消订单时，退还积分
     */
    #[Async]
    #[AsEventListener]
    public function payBackCredit(AfterOrderCancelEvent $event): void
    {
        // 取消订单时，我们要调用远程接口补还积分
        foreach ($event->getContract()->getPrices() as $price) {
            $this->orderService->refundPrice($price);
        }

        // 保存一次
        $this->entityManager->persist($event->getContract());
        $this->entityManager->flush();
    }
}
