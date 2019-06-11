<?php
/**
 * @package   Divante\VsbridgeIndexerMsi
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerMsi\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service which returns linked stock Id for a website sales channel code
 */
interface GetStockIdBySalesChannelCodeInterface
{
    /**
     * @param string $code
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function execute(string $code): int;
}
