<?php

namespace OrderCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use OrderCoreBundle\Entity\OrderContact;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class OrderContactFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['order', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        $contactData = [
            [
                'name' => '张经理',
                'phone' => '13900139001',
                'email' => 'zhang.manager@company.com',
                'position' => '销售经理',
                'department' => '销售部',
                'contactType' => 'primary',
                'isActive' => true,
            ],
            [
                'name' => '李助理',
                'phone' => '13900139002',
                'email' => 'li.assistant@company.com',
                'position' => '销售助理',
                'department' => '销售部',
                'contactType' => 'secondary',
                'isActive' => true,
            ],
            [
                'name' => '王客服',
                'phone' => '13900139003',
                'email' => 'wang.service@company.com',
                'position' => '客服专员',
                'department' => '客服部',
                'contactType' => 'support',
                'isActive' => true,
            ],
            [
                'name' => '赵主管',
                'phone' => '13900139004',
                'email' => 'zhao.supervisor@company.com',
                'position' => '物流主管',
                'department' => '物流部',
                'contactType' => 'logistics',
                'isActive' => false,
            ],
        ];

        foreach ($contactData as $index => $data) {
            $contact = new OrderContact();
            $contact->setName($data['name']);
            $contact->setRealname($data['name']);
            $contact->setPhone($data['phone']);
            $contact->setEmail($data['email']);
            $contact->setPosition($data['position']);
            $contact->setDepartment($data['department']);
            $contact->setContactType($data['contactType']);
            $contact->setIsActive($data['isActive']);

            $manager->persist($contact);
            $this->addReference('order-contact-' . $index, $contact);
        }

        $manager->flush();
    }
}
