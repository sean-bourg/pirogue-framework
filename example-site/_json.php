<?php
use function sprout\__import;
use function sprout\import;
use function sprout\database_collection_open;
use function sprout\_error_handler;
use function sprout\_dispatcher_send;
use function sprout\__database_collection;
use function sprout\dispatcher_set_cache_control;
use function sprout\_json_route;

/**
 * Main dispatcher for JSON content.
 *
 * Processes user request and routes it to the proper function found the requested module file in _json/[ModuleName].inc - handles any REST method
 * that the developer writes end point for.
 *
 * @example For any REST method:
 *          site.url/module/function/function_data.json?data=my_data
 *         
 *          include(_json/module.inc);
 *         
 *          For GET REQUEST: module/GET_function(function_data, request_data);
 *          For POST REQUEST: module/POST_function(function_data, request_data, post_data);
 *          For PUT REQUEST: module/PUT_function(function_data, request_data, post_data);
 *          For DELETE REQUEST: module/DELETE_function(function_data, request_data);
 *          For OPTIONS REQUEST: module/DELETE_function(function_data, request_data);
 *         
 *         
 * @author Bourg, Sean P. <sean.bourg@gmail.com>
 */

// Send request headers (type for this dispatcher):
header('Content-Type: application/json', true);

define('_BASE_URI', __DIR__);

// Load & intialize sprout framework:
require '_include/sprout/import.inc';
__import(sprintf('%s\_include', _BASE_URI));

// Import required libraries
import('sprout/dispatcher');
import('sprout/error_handler');
import('sprout/database_collection');
import('sprout/json');

set_error_handler('sprout\_error_handler');

$GLOBALS['._dispatcher_failsafe_exception'] = null;

try {
    /* Initialize */
    __database_collection(realpath('_config'));

    /* Parse request */
    $_request_data = $_GET;
    $_request_path = $_request_data['__execution_path'] ?? '';
    unset($_request_data['__execution_path']);

    /* Route request and send results to client */
    return _dispatcher_send(_json_route($_SERVER['REQUEST_METHOD'], $_request_path, $_request_data, ('POST' == $_SERVER['REQUEST_METHOD']) ? $_POST : []));
} catch (Error $_exception) {
    $GLOBALS['._dispatcher_failsafe_exception'] = $_exception;
} catch (Exception $_exception) {
    $GLOBALS['._dispatcher_failsafe_exception'] = $_exception;
}

// Failsafe errors:
http_response_code(500);
if ($GLOBALS['._dispatcher_failsafe_exception']) {
    echo json_encode(sprintf('%s: (%s:%d)', $GLOBALS['._dispatcher_failsafe_exception']->getMessage(), str_replace(_BASE_URI, '', $GLOBALS['._dispatcher_failsafe_exception']->getFile()), $GLOBALS['._dispatcher_failsafe_exception']->getLine()));
} else {
    echo json_encode('Unknown exception encountered');
}