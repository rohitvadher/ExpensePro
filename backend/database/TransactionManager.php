<?php


function epTransaction(PDO $pdo, callable $work): mixed
{
    $managed = !$pdo->inTransaction();
    if ($managed) {
        $pdo->beginTransaction();
    }
    try {
        $result = $work($pdo);
        if ($managed) {
            $pdo->commit();
        }
        return $result;
    } catch (Throwable $e) {
        if ($managed && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
