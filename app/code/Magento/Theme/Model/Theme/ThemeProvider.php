<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

class ThemeProvider implements \Magento\Framework\View\Design\Theme\ThemeProviderInterface
{
    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Theme\Model\ThemeFactory
     */
    protected $themeFactory;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * ThemeProvider constructor.
     *
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->themeFactory = $themeFactory;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeByFullPath($fullPath)
    {
        /** @var $themeCollection \Magento\Theme\Model\ResourceModel\Theme\Collection */
        $theme = $this->cache->load('theme'. $fullPath);
        if ($theme) {
            return unserialize($theme);
        }
        $themeCollection = $this->collectionFactory->create();
        $item = $themeCollection->getThemeByFullPath($fullPath);
        if ($item->getId()) {
            $themeData = serialize($item);
            $this->cache->save($themeData, 'theme' . $fullPath);
            $this->cache->save($themeData, 'theme-by-id-' . $item->getId());
        }
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeCustomizations(
        $area = \Magento\Framework\App\Area::AREA_FRONTEND,
        $type = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
    ) {
        /** @var $themeCollection \Magento\Theme\Model\ResourceModel\Theme\Collection */
        $themeCollection = $this->collectionFactory->create();
        $themeCollection->addAreaFilter($area)->addTypeFilter($type);
        return $themeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeById($themeId)
    {
        $theme = $this->cache->load('theme-by-id-' . $themeId);
        if ($theme) {
            return unserialize($theme);
        }
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $themeModel = $this->themeFactory->create();
        $themeModel->load($themeId);
        if ($themeModel->getId()) {
            $this->cache->save(serialize($themeModel), 'theme-by-id-' . $themeId);
        }
        return $themeModel;
    }
}
