<?php

namespace App\Contracts\Services\Ecommerce\Dao;

use App\Exceptions\Services\Ecommerce\InvalidDaoException;
use Illuminate\Support\Facades\Validator;

/**
 * The abstract base class for every e-commerce Data Access Object (DAO)
 *
 * TODO: For this to be a fully-fledged mutable CRUD (create, read, update, delete) object
 *       there is a need for the implementation the "update" and "delete" functionality.
 *       "create" is served by the constructor, while "read" is handled in the
 *       {@see BaseDao::get()} and {@see BaseDao::has()} methods at the abstraction level, and
 *       {@see BaseDao::find()} and {@see BaseDao::fetch()} at the external service level.
 *       When completed, the bulk of this message should be used in the main description for this
 *       class.
 */
abstract class BaseDao
{
    /**
     * A set of rules compliant with the {@see Validator} facade applied to {@see BaseDao::data}
     */
    protected const DATA_VALIDATION_RULES = [];

    /**
     * Populates the DAO with its data values then validates the data, ensuring
     * that all expected values are present
     *
     * @param array $data An associative array of data keys and values for the DAO
     *
     * @throws InvalidDaoException if the DAO does not have all the necessary data
     *                              in the required format
     */
    function __construct(protected array $data = [])
    {
        $validator = Validator::make($data, static::DATA_VALIDATION_RULES);

        if ($validator->fails()) {
            $errorStack = '';
            foreach($validator->errors()->getMessages() as $key => $errorMessages) {
                $errorStack .= "$key:\n";

                foreach ($errorMessages as $errorMessage) {
                    $errorStack .= "\t$errorMessage\n";
                }
            }

            throw new InvalidDaoException($errorStack);
        }
    }

    /**
     * Retrieves a data attribute of the DAO
     *
     * @param ?string $key to an associated data value.  Can be null
     *                     to return all data values
     *
     * @return mixed The value associated with the key.  If the key
     *               does not exist, then null will be returned. If
     *               no key was given, an array of all data will be
     *               returned
     */
    final public function get(?string $key = null): mixed
    {
        return is_null($key)
            ? $this->data
            : $this->data[$key] ?? null;
    }

    /**
     * Determines if a data key has been set
     *
     * @param string $key of the data entry to be checked
     *
     * @return bool true if the data key exists, otherwise false
     */
    final public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Fetches a collection of this entity as DAOs from the e-commerce service that matches the filters
     *
     * @param array $filters The search criteria and data structure hints used to filter this entity type.
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity type
     *
     * @return BaseDao[] a collection containing all entities that match the filter criteria
     */
    abstract public static function fetch(array $filters = [], mixed ...$extraParameters): array;

    /**
     * Finds this entity from the e-commerce service by ID and returns it as a DAO
     *
     * @param string|int $id The ID of the entity to retrieve
     * @param mixed $extraParameters any extra information needed to query the e-commerce service
     *                               for this entity
     *
     * @return ?BaseDao the found entity
     */
    abstract public static function find(string|int $id, mixed ...$extraParameters): ?BaseDao;

    /**
     * Given a 3rd-party representation of the same object, converts it to our
     * common native abstraction
     *
     * @param mixed $externalEntity is the 3rd-party representation of this object
     *
     * @return BaseDao our native common representation of this object
     */
    abstract protected static function createFromExternal(mixed $externalEntity): BaseDao;
}
