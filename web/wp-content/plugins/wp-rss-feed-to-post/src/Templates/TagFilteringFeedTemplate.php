<?php

namespace RebelCode\Wpra\FeedToPost\Templates;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;

/**
 * A decorator for the WP RSS Aggregator feed template, adding the ability to select posts by tags.
 *
 * @since 3.11
 */
class TagFilteringFeedTemplate implements TemplateInterface
{
    /**
     * The inner template.
     *
     * @since 3.11
     *
     * @var TemplateInterface
     */
    protected $inner;

    /**
     * The collection of feed items.
     *
     * @since 3.11
     *
     * @var CollectionInterface
     */
    protected $itemsCollection;

    /**
     * Constructor.
     *
     * @since 3.11
     *
     * @param TemplateInterface   $inner           The inner template.
     * @param CollectionInterface $itemsCollection The collection of feed items.
     */
    public function __construct($inner, $itemsCollection)
    {
        $this->inner = $inner;
        $this->itemsCollection = $itemsCollection;
    }

    /**
     * @inheritdoc
     *
     * @since 3.11
     */
    public function render($ctx = null)
    {
        $arrCtx = (is_array($ctx) || is_object($ctx)) ? (array) $ctx : $ctx;

        $items = (empty($ctx['items']) || !($ctx['items'] instanceof CollectionInterface))
            ? $this->itemsCollection
            : $ctx['items'];

        if (isset($arrCtx['tags'])) {
            $tags = $arrCtx['tags'];
            $tags = is_string($tags) ? array_map('trim', explode(',', $tags)) : $tags;

            $items = $items->filter(['tag_slug__in' => $tags]);
        }

        $ctx['items'] = $items;

        return $this->inner->render($ctx);
    }
}
