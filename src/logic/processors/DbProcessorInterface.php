<?php


namespace floor12\backup\logic\processors;


interface DbProcessorInterface
{
    public function backup(): void;

    public function restore(array $tableNames = []): void;

    public function getTables(): array;
}