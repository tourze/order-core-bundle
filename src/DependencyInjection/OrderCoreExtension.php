<?php

namespace OrderCoreBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class OrderCoreExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }

    //    public function prepend(ContainerBuilder $container): void
    //    {
    //        // TODO 还没真正用上
    //        $container->prependExtensionConfig('framework', [
    //            'workflows' => [
    //                'enabled' => true,
    //                'blog_publishing' => [
    //                    'type' => 'workflow',
    //                    'audit_trail' => [
    //                        'enabled' => true,
    //                    ],
    //                    'marking_store' => [
    //                        'type' => 'method',
    //                        'property' => 'state',
    //                    ],
    //                    'supports' => [
    //                        Contract::class,
    //                    ],
    //                    'initial_marking' => 'draft',
    //                    'places' => [
    //                        'draft',
    //                        'reviewed',
    //                        'rejected',
    //                        'published',
    //                    ],
    //                    'transitions' => [
    //                        'to_review' => [
    //                            'from' => 'draft',
    //                            'to' => 'reviewed',
    //                        ],
    //                        'publish' => [
    //                            'from' => 'reviewed',
    //                            'to' => 'published',
    //                        ],
    //                        'reject' => [
    //                            'from' => 'reviewed',
    //                            'to' => 'rejected',
    //                        ],
    //                    ],
    //                ],
    //            ],
    //        ]);
    //    }
}
