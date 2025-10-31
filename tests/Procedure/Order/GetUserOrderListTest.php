<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Procedure\Order\GetUserOrderList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetUserOrderList::class)]
#[RunTestsInSeparateProcesses]
class GetUserOrderListTest extends AbstractProcedureTestCase
{
    private GetUserOrderList $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetUserOrderList::class);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(GetUserOrderList::class, $this->procedure);
    }

    public function testExecuteReturnsUserOrderList(): void
    {
        // 创建并设置认证用户
        $user = $this->createNormalUser();
        $this->setAuthenticatedUser($user);

        // 设置分页参数
        $this->procedure->currentPage = 1;
        $this->procedure->pageSize = 10;

        $result = $this->procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result, '应该包含list字段');
        $this->assertArrayHasKey('pagination', $result, '应该包含pagination字段');
        $this->assertIsArray($result['list']);
        $this->assertIsArray($result['pagination']);
    }
}
