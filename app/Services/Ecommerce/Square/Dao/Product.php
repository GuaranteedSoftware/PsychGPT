<?php

namespace App\Services\Ecommerce\Square\Dao;

use App\Contracts\Services\Ecommerce\Dao\BaseDao;
use App\Contracts\Services\Ecommerce\Dao\Product as EcommerceProduct;
use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use App\Exceptions\Services\Ecommerce\InvalidInputException;
use App\Exceptions\Services\Ecommerce\InvalidStateException;
use App\Exceptions\Services\Ecommerce\RetrievalException;
use App\Services\Ecommerce\Square\SquareService;
use Square\Models\CatalogObject;
use Square\Models\CatalogObject as SquareProduct;
use Square\Models\CatalogQuery;
use Square\Models\CatalogQueryPrefix;
use Square\Models\SearchCatalogObjectsRequest;

/**
 * A Square implementation of our e-commerce product abstraction
 */
class Product extends EcommerceProduct
{
    /**
     * Indicates that the product entity should be returned as a native Square {@see CatalogObject}.
     *
     * @see Product::fetchAs()
     * @see Product::findAs()
     */
    public const AS_SQUARE_OBJECT = 1;

    /**
     * Indicates that the product entity should be returned as this native implementation of the
     * {@see \App\Contracts\Services\Ecommerce\Dao\Product} abstraction.
     *
     * @see Product::fetchAs()
     * @see Product::findAs()
     */
    public const AS_DAO = 2;

    /**
     * Fetches a collection of API objects from this e-commerce service, matching the search criteria
     *
     * @param int $format the format of fetch objects to return, either as a native Square entity or a contracted DAO
     * @param array $filters The search criteria and formatting hints used to filter catalog objects.
     *                       Details for catalog objects searching can be read at
     *                       {@link https://developer.squareup.com/docs/catalog-api/search-catalog-objects}
     *                       We will support searches for 'object_types' and 'name'
     * @param array $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type.  An array with the integer 'limit' is supported
     *                               which limits the number of results that are returned
     *
     * @return Product[]|SquareProduct[] array containing all DAOs or native objects created from the Square API
     *                                   entities that match the filter criteria
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if a Square product that is fetched does not have all the required info to
     *                               make a local {@see Product}
     * @throws RetrievalException if there is a problem querying Square
     *
     * @see self::AS_SQUARE_OBJECT for $format
     * @see self::AS_DAO  for $format
     */
    public static function fetchAs(int $format = self::AS_SQUARE_OBJECT, array $filters = [], ...$extraParameters): array
    {
        $catalogSearchRequest = new SearchCatalogObjectsRequest();
        $catalogSearchRequest->setObjectTypes($filters['object_types'] ?? ['ITEM']);

        if ($productName = $filters['name'] ?? null) { # intentional assignment
            $catalogQueryPrefix = new CatalogQueryPrefix('name', $productName);
            $catalogQuery = new CatalogQuery();
            $catalogQuery->setPrefixQuery($catalogQueryPrefix);
            $catalogSearchRequest->setQuery($catalogQuery);
        }

        if ($limit = $extraParameters[0]['limit']) { # intentional assignment
            $catalogSearchRequest->setLimit($limit);
        }

        $response = SquareService::client()->getCatalogApi()->searchCatalogObjects($catalogSearchRequest);

        if ($response->isError()) {
            throw new RetrievalException('Unable to communicate successfully with Square.');
        }

        $daos = [];
        foreach ($response->getResult()->getObjects() ?? [] as $product) {
            $daos[] = ($format === self::AS_SQUARE_OBJECT) ? $product : static::createFromExternal($product);
        }

        return $daos;
    }

    /**
     * Fetches a collection of API objects from this e-commerce service, matching the search criteria
     *
     * @param array $filters The search criteria and formatting hints used to filter catalog objects.
     *                       Details for catalog objects searching can be read at
     *                       {@link https://developer.squareup.com/docs/catalog-api/search-catalog-objects}
     *                       We will support searches for 'object_types' and 'name'
     * @param array $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type.  An array with the integer 'limit' is supported
     *                               which limits the number of results that are returned
     *
     * @return Product[] array containing all DAOs or native objects created from the Square API
     *                   entities that match the filter criteria
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if a Square product that is fetched does not have all the required info to
     *                               make a local {@see Product}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function fetch(array $filters = [], mixed ...$extraParameters): array
    {
        return static::fetchAs(self::AS_DAO, $filters, $extraParameters);
    }

    /**
     * Gets a native representation of an entity from the Square API
     *
     * @param int $format the format of fetch objects to return, either as a native Square entity or a contracted DAO
     * @param string|int $id The ID of the object to retrieve
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return BaseDao|CatalogObject|null The found entity
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if the Square product that is found does not have all the required info to
     *                               make a local {@see Product}
     * @throws RetrievalException if there is a problem querying Square
     *
     * @see self::AS_SQUARE_OBJECT for $format
     * @see self::AS_DAO  for $format
     */
    public static function findAs(int $format, string|int $id, mixed ...$extraParameters): Product|CatalogObject|null
    {
        $response = SquareService::client()->getCatalogApi()->retrieveCatalogObject($id);

        if ($response->isError()) {
            throw new RetrievalException($response->getErrors()[0]->getDetail());
        }

        $product = $response->getResult()->getObject();

        if (!$product) {
            return null;
        }

        if ($format === self::AS_SQUARE_OBJECT) {
            return $product;
        }

        return static::createFromExternal($product);
    }

    /**
     * Gets a native representation of an entity from the Square API
     *
     * @param string|int $id The ID of the object to retrieve
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return ?Product The found entity
     *
     * @throws InvalidInputException if the Square entity does not match a DAO that we attempt to create
     * @throws InvalidStateException if the Square product that is found does not have all the required info to
     *                               make a local {@see Product}
     * @throws RetrievalException if there is a problem querying Square
     */
    public static function find(string|int $id, mixed ...$extraParameters): ?Product
    {
        return static::findAs(self::AS_DAO, $id);
    }

    /**
     * Given a {@see SquareProduct}, converts it to our product abstraction
     *
     * @param SquareProduct $squareProduct is a Stripe SDK product to convert to a product DAO
     *
     * @return Product is a generalized contracted object created using the external representation as the reference
     *
     * @throws InvalidDaoException if an attempt is made to create a product without all the required data
     * @throws InvalidInputException if the $squareProduct is not a {@see SquareProduct}
     */
    protected static function createFromExternal(mixed $squareProduct): Product
    {
        if (!($squareProduct instanceof SquareProduct)) {
            throw new InvalidInputException(
                'The external entity must be a ' . SquareProduct::class
                    . '. A ' . $squareProduct::class . ' was passed instead.'
            );
        }

        $productData = [
            'id' => $squareProduct->getId(),
            'name' => $squareProduct->getItemVariationData()->getName(),
            'price' => $squareProduct->getItemVariationData()->getPriceMoney()->getAmount(),
            'location_id' => $squareProduct->getPresentAtLocationIds()[0],
        ];

        return new Product($productData);
    }
}
