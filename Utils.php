<?php

    namespace TechorAko\Database;

    class Utils {
        public static function parseValue($value): string {
            if (is_numeric($value)) {
                return "$value";
            } elseif ($value === NULL) {
                return 'NULL';
            } elseif (is_bool($value)) {
                return $value ? "1" : "0";
            } else {
                $value = "'$value'";
                return $value;
            }
        }

        public static function parseIdentifier(string $identifier): string {
            return "`$identifier`";
        }

        public static function canIdentifierParse(string $identifier): bool {
            $identifier = trim($identifier);
            $isParsed = (str_starts_with($identifier, '`') === false) && (str_ends_with($identifier, '`') === false);
            $isWildCard = ($identifier === "*");
            $isFunction = str_contains("(", $identifier) && str_contains(")", $identifier);
            return !$isParsed || !$isWildCard || !$isFunction;
        }

        public static function isMultipleValues(array $values) {
            return !empty(array_filter($values,'is_array'));
        }

        public static function associativeValues(array &$values, ?array &$keys): void {
            if (!array_is_list($values) && empty($keys)) {
                $keys = array_keys($values);
            }
            if (array_is_list($values) && !empty($keys)) {
                $values = array_combine($keys, $values);
            }
        }

        public static function implodeInsert(array $array): string {
            return "(". self::implodeValues($array) .")";
        }

        public static function implodeValues(array $values): string {
            return implode(", ", $values);
        }

        public static function formatUpdate(array $values, array $columns): string {
            $values = array_combine($columns, $values);
            foreach ($values as $column => $value) {
                $updates[] = $column . " = $value";
            }
            $updates = implode(", ", $updates);
            return $updates;
        }

        public static function qualifyColumn(string $column, string $table): string {
            if (empty($table) || empty($column)) {
                throw new \InvalidArgumentException('Table or column name cannot be empty.');
            }
            return "$table.$column";
        }

        public static function qualifyColumnsArray(array $columns, array|string $tables): array {
            if (is_string($tables)) {
                foreach ($columns as &$column) {
                    $column = self::qualifyColumn($column, $tables);
                }
                return $columns;
            }
    
            if (is_array($tables)) {
                if (count($tables) !== count($columns)) {
                    throw new \InvalidArgumentException('The number of tables and columns must match.');
                }
                $columns = array_combine($tables, $columns);
                foreach ($columns as $table => &$column) {
                    $column = self::qualifyColumn($column, $table);
                }
                return $columns;
            }
    
            throw new \InvalidArgumentException('Tables must be either a string or an array.');
        }

        public static function assignAlias(string $identifier, string $alias): string {
            return "$identifier as $alias";
        }

        public static function assignAliasesArray(array $identifiers, array $aliases): array {
            if (count($aliases) !== count($identifiers)) {
                throw new \InvalidArgumentException('The number of aliases and identifiers must match.');
            }
            $identifiers = array_combine($aliases, $identifiers);
            foreach ($identifiers as $alias => &$identifier) {
                $identifier = self::assignAlias($identifier, $alias);
            }
            return $identifiers;
        }

        public static function fillPlaceHolders(array $columns, string $prefix = ":") {
            $columns = array_flip($columns);
            foreach ($columns as $column => &$value) {
                $value = $prefix.$column;
            }
            return $columns;
        }
    }

?>