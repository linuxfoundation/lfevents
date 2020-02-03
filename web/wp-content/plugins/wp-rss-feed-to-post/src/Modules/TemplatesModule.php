<?php

namespace RebelCode\Wpra\FeedToPost\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\ModuleInterface;
use RebelCode\Wpra\FeedToPost\Templates\TagFilteringFeedTemplate;

/**
 * The module that extends the core template system.
 *
 * @since 3.11
 */
class TemplatesModule implements ModuleInterface
{
    /**
     * @inheritdoc
     *
     * @since 3.11
     */
    public function getFactories()
    {
        return [];
    }

    /**
     * @inheritdoc
     *
     * @since 3.11
     */
    public function getExtensions()
    {
        return [
            /*
             * Extends the core master template with a decorator that can handle keyword filtering context args.
             *
             * @since 3.11
             */
            'wpra/feeds/templates/master_template' => function (ContainerInterface $c, $prev) {
                $collection = $c->get('wpra/feeds/templates/feed_item_collection');

                return new TagFilteringFeedTemplate($prev, $collection);
            },
        ];
    }

    /**
     * @inheritdoc
     *
     * @since 3.11
     */
    public function run(ContainerInterface $c)
    {
    }
}
