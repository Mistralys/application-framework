<?php
/**
 * Stub endpoint used for Connector request tests.
 * Example: {@see AppFrameworkTests\Connectors\RequestTests}.
 *
 * @package Application Tests
 * @subpackage Connectors
 */

declare(strict_types=1);

use AppUtils\ConvertHelper\JSONConverter;

require_once __DIR__.'/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

http_response_code(200);

$entityBody = file_get_contents('php://input');
$data = null;
if(!empty($entityBody)) {
    $data = JSONConverter::json2array($entityBody);
}

echo JSONConverter::var2json(array(
    'status' => 'OK',
    'data' => $data
));

exit;
