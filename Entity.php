<?php

    namespace TechorAko\Database\Table;

    require_once 'Utils.php';
    require_once 'dbTable.php';

    use TechorAko\Database\Table;
    use TechorAko\Database\Utils;

    class Entity {
        public Table $table;
        private array $values = [];
        public function __construct(Table $table, ?array $values = [], ?array $attributes = []) {
            $this->table = $table;
            $this->setValues($values, $attributes);
        }

        public function setValues(array $values, ?array $attributes = []) {
            Utils::associativeValues($values, $attributes);
            $tableAttributes = $this->table->getAttributes();
            if (!in_array($attributes, $tableAttributes)) {
                $this->table->addAttributes(...$attributes);
            }
            $this->values = $values;
        }

        public function getValues(): array {
            return $this->values;
        }

        public function getAttributes(): array {
            $values = $this->values;
            $tableAttributes = $this->table->getAttributes();
            if (!array_is_list($values)) {
                $attributes = array_keys($values);
                return $attributes;
            } else if (count($values) <= count($tableAttributes)) {
                return $tableAttributes;
            }
            throw new \Exception("Insufficient attributes or columns.");
        }

        public function getPrimaryKeyValues(): array {
            $primaryKey = $this->table->getPrimaryKey();
            $values = $this->getValues();

            $primaryKeyValues = array_filter($values, function ($key) use ($primaryKey) {
                    return in_array($key, $primaryKey, true);
            }, ARRAY_FILTER_USE_KEY);

            return $primaryKeyValues;
        }

        public function getWherePrimaryKey(): string {
            $values = $this->getPrimaryKeyValues();
            if (empty($values)) {
                throw new \Exception("No primary keys found.");
            }
            foreach ($values as $column => &$value) {
                $column = Utils::parseIdentifier($column);
                $value = "$column = $value";
            }
            return "WHERE " . implode(" AND ", $values);
        }

        public function insert(?array $values = [], ?array $attributes = []): string {
            $tableName = Utils::parseIdentifier($this->table->name);
            $attributes = Utils::implodeInsert(array_map([Utils::class, 'parseIdentifier'], $this->getAttributes())) ?? $attributes;
            $values = $this->getValues() ?? $values;
            $query = "INSERT INTO $tableName $attributes VALUES $values";
            return $query;
        }

        public function select(?array $attributes = []): string {
            $tableName = Utils::parseIdentifier($this->table->name);
            $attributes = implode(", ", array_map([Utils::class, 'parseIdentifier'], $this->getAttributes())) ?? $attributes;
            $query = "SELECT $attributes FROM $tableName " . $this->getWherePrimaryKey();
            return $query;
        }

        public function update(?array $values = [], ?array $attributes = []): string {
            $tableName = Utils::parseIdentifier($this->table->name);
            $attributes = array_map([Utils::class, 'parseIdentifier'], $this->getAttributes()) ?? $attributes;
            $values = $this->getValues() ?? $values;
            $this->setValues($values, $attributes);
            $update = Utils::formatUpdate($values, $attributes);
            $query = "UPDATE $tableName SET $update " . $this->getWherePrimaryKey();
            return $query;
        }

        public function delete(): string {
            $tableName = Utils::parseIdentifier($this->table->name);
            $query = "DELETE FROM $tableName " . $this->getWherePrimaryKey();
            return $query;
        }
    }

?>