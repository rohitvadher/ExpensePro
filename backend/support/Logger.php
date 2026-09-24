<?php

function epEnsureLogDirectories(): void
{
}

function epLogFile(string $name = 'error.log'): string
{
    return '';
}

function epLog(string $message, string $file = 'error.log'): void
{
}

function epLogException(Throwable $e, string $context = ''): void
{
}
