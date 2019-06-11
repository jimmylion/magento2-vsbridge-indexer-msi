<?php
/**
 * @package  Divante\VsbridgeIndexerMsi
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerMsi\Plugin\InventoryCatalog;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;

use Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization\SetDataToLegacyCatalogInventory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

/**
 * Class SetDataToLegacyCatalogInventoryPlugin
 */
class SetDataToLegacyCatalogInventoryPlugin
{
    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var ProductsForReindex
     */
    private $productsForReindex;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * ProcessStockChangedPlugin constructor.
     *
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param ProductsForReindex $itemsForReindex
     * @param ProductProcessor $processor
     */
    public function __construct(
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        ProductsForReindex $itemsForReindex,
        ProductProcessor $processor
    ) {
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->productsForReindex = $itemsForReindex;
        $this->productProcessor = $processor;
    }

    /**
     * @param SetDataToLegacyCatalogInventory $subject
     * @param callable $proceed
     * @param array $sourceItems
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(SetDataToLegacyCatalogInventory $subject, callable $proceed, array $sourceItems)
    {
        $productIds = [];

        foreach ($sourceItems as $sourceItem) {
            $sku = $sourceItem->getSku();

            try {
                $productId = (int)$this->getProductIdsBySkus->execute([$sku])[$sku];
            } catch (NoSuchEntityException $e) {
                // Skip synchronization of for not existed product
                continue;
            }

            $productIds[] = $productId;
        }

        $this->productsForReindex->setProducts($productIds);

        $proceed($sourceItems);
    }

    /**
     * @param SetDataToLegacyCatalogInventory $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SetDataToLegacyCatalogInventory $subject)
    {
        $products = $this->productsForReindex->getProducts();

        if (!empty($products)) {
            $this->productProcessor->reindexList($products);
            $this->productsForReindex->clear();
        }
    }
}
