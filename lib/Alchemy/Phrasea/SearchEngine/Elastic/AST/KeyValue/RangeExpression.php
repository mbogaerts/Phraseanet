<?php

namespace Alchemy\Phrasea\SearchEngine\Elastic\AST\KeyValue;

use Alchemy\Phrasea\SearchEngine\Elastic\AST\StringHelper;
use Assert\Assertion;
use Alchemy\Phrasea\SearchEngine\Elastic\AST\Node;
use Alchemy\Phrasea\SearchEngine\Elastic\Exception\QueryException;
use Alchemy\Phrasea\SearchEngine\Elastic\Search\QueryContext;
use Alchemy\Phrasea\SearchEngine\Elastic\Search\QueryPostProcessor;
use Alchemy\Phrasea\SearchEngine\Elastic\Structure\Field as StructureField;
use Alchemy\Phrasea\SearchEngine\Elastic\FieldMapping;
use Alchemy\Phrasea\SearchEngine\Elastic\Search\QueryHelper;

class RangeExpression extends Node
{
    /** @var FieldKey  */
    private $key;

    private $lower_bound;
    private $lower_inclusive;
    private $higher_bound;
    private $higher_inclusive;

    public static function lessThan(Key $key, $bound)
    {
        return new self($key, null, false, $bound, false);
    }

    public static function lessThanOrEqual(Key $key, $bound)
    {
        return new self($key, null, false, $bound, true);
    }

    public static function greaterThan(Key $key, $bound)
    {
        return new self($key, $bound, false);
    }

    public static function greaterThanOrEqual(Key $key, $bound)
    {
        return new self($key, $bound, true);
    }

    public function __construct(Key $key, $lb, $li = false, $hb = null, $hi = false)
    {
        Assertion::nullOrScalar($lb);
        Assertion::boolean($li);
        Assertion::nullOrScalar($hb);
        Assertion::boolean($hi);
        $this->key = $key;
        $this->lower_bound = StringHelper::unescape($lb);
        $this->lower_inclusive = $li;
        $this->higher_bound = StringHelper::unescape($hb);
        $this->higher_inclusive = $hi;
    }

    public function buildQuery(QueryContext $context)
    {
        $params = array();
        /** @var StructureField $field */
        // $field = $this->key->getField($context);
        $lower_bound = $this->lower_bound;
        $higher_bound = $this->higher_bound;

        if($this->key->getFieldType($context) === FieldMapping::TYPE_DATE) {
            if($lower_bound !== null) {
                $lower_bound = QueryHelper::sanitizeDate($lower_bound);
            }
            if($higher_bound !== null) {
                $higher_bound = QueryHelper::sanitizeDate($higher_bound);
            }
        }

        if ($lower_bound !== null) {
            $this->assertValueCompatible($lower_bound, $context);
            if ($this->lower_inclusive) {
                $params['gte'] = $lower_bound;
            } else {
                $params['gt'] = $lower_bound;
            }
        }
        if ($higher_bound !== null) {
            $this->assertValueCompatible($higher_bound, $context);
            if ($this->higher_inclusive) {
                $params['lte'] = $higher_bound;
            } else {
                $params['lt'] = $higher_bound;
            }
        }

        $query = [];
        $query['range'][$this->key->getIndexField($context)] = $params;

        if ($this->key instanceof QueryPostProcessor) {
            return $this->key->postProcessQuery($query, $context);
        }

        return $query;
    }

    private function assertValueCompatible($value, QueryContext $context)
    {
        if (!$this->key->isValueCompatible($value, $context)) {
            throw new QueryException(sprintf('Value "%s" for key "%s" is not valid.', $value, $this->key));
        }
    }

    public function getTermNodes()
    {
        return array();
    }

    public function __toString()
    {
        $string = '';
        if ($this->lower_bound !== null) {
            if ($this->lower_inclusive) {
                $string .= sprintf(' gte="%s"', $this->lower_bound);
            } else {
                $string .= sprintf(' gt="%s"', $this->lower_bound);
            }
        }
        if ($this->higher_bound !== null) {
            if ($this->higher_inclusive) {
                $string .= sprintf(' lte="%s"', $this->higher_bound);
            } else {
                $string .= sprintf(' lt="%s"', $this->higher_bound);
            }
        }

        return sprintf('<range:%s%s>', $this->key, $string);
    }
}
