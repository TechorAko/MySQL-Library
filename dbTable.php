<?php

    namespace TechorAko\Database;

    class Table {
        public string $name;
        private array $attributes = [];
        private array $primaryKey = [];

        public function __construct(string $name, ?array $attributes = [], ?array $primaryKey = []) {
            $this->name = $name;
            $this->setAttributes($attributes ?? []);
            $this->setPrimaryKey($primaryKey ?? []);
        }

        public function setAttributes(array $attributes): void {
            $this->attributes = array_unique($attributes);
        }

        public function getAttributes(): array {
            return $this->attributes;
        }

        public function addAttributes(string ...$attributes): void {
            $this->setAttributes(array_merge($this->attributes, $attributes));
        }

        public function removeAttributes(string ...$attributes): void {
            $attributesToRemove = array_flip($attributes);
            $this->setAttributes(array_values(array_filter($this->attributes, function($attribute) use ($attributesToRemove) {
                return !isset($attributesToRemove[$attribute]);
            })));
        }

        public function setPrimaryKey(array $columns): void {
            $this->primaryKey = array_unique($columns);
            $this->addAttributes(...$columns); // Ensure primary keys are in attributes
        }

        public function getPrimaryKey(): array {
            return $this->primaryKey;
        }

        public function addPrimaryKeyColumns(string ...$columns): void {
            $this->setPrimaryKey(array_merge($this->primaryKey, $columns));
            $this->addAttributes(...$columns); // Ensure primary keys are in attributes
        }

        public function removePrimaryKeyColumn(string ...$columns): void {
            $columnsToRemove = array_flip($columns);
            $this->setPrimaryKey(array_values(array_filter($this->primaryKey, function($column) use ($columnsToRemove) {
                return !isset($columnsToRemove[$column]);
            })));
        }
    }

?>
