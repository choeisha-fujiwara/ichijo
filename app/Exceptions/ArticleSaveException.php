<?php

namespace App\Exceptions;

/**
 * メッセージをそのままユーザーに表示してよい、原因が特定済みの記事保存エラー。
 */
class ArticleSaveException extends \RuntimeException
{
}
