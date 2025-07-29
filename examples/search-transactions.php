<?php

require_once __DIR__.'/../vendor/autoload.php';

use Asciisd\Cybersource\Facades\Cybersource;

// Example: Search for transactions by client reference code
$searchParams = [
    'query' => 'clientReferenceInformation.code:01k19gqb9kbw1zta0n65vfgmrv',
    'name' => 'Order Search',
    'timezone' => 'America/Chicago',
    'offset' => 0,
    'limit' => 100,
    'sort' => 'submitTimeUtc:desc',
];

try {
    $result = Cybersource::searchTransactions($searchParams);

    echo "Search Results:\n";
    echo "===============\n";
    echo 'Search ID: '.$result['searchId']."\n";
    echo 'Total Count: '.$result['totalCount']."\n";
    echo 'Count: '.$result['count']."\n";
    echo 'Limit: '.$result['limit']."\n";
    echo 'Offset: '.$result['offset']."\n\n";

    if (isset($result['_embedded']['transactionSummaries'])) {
        echo "Transactions:\n";
        echo "=============\n";

        foreach ($result['_embedded']['transactionSummaries'] as $transaction) {
            echo 'Transaction ID: '.$transaction['id']."\n";
            echo 'Submit Time: '.$transaction['submitTimeUtc']."\n";
            echo 'Reference Code: '.($transaction['clientReferenceInformation']['code'] ?? 'N/A')."\n";
            echo 'Reason Code: '.($transaction['applicationInformation']['reasonCode'] ?? 'N/A')."\n";
            echo "---\n";
        }
    } else {
        echo "No transactions found.\n";
    }

} catch (Exception $e) {
    echo 'Error searching transactions: '.$e->getMessage()."\n";
}

// Example: Search for transactions within a date range
echo "\n\nSearching for transactions in date range:\n";
echo "=========================================\n";

$dateRangeSearch = [
    'query' => 'submitTimeUtc:[2024-01-01T00:00:00Z TO 2024-12-31T23:59:59Z]',
    'name' => 'Date Range Search',
    'timezone' => 'UTC',
    'limit' => 10,
    'sort' => 'submitTimeUtc:desc',
];

try {
    $result = Cybersource::searchTransactions($dateRangeSearch);
    echo 'Found '.$result['totalCount']." transactions in the specified date range.\n";
} catch (Exception $e) {
    echo 'Error searching transactions: '.$e->getMessage()."\n";
}

// Example: Search for transactions by amount
echo "\n\nSearching for transactions by amount:\n";
echo "====================================\n";

$amountSearch = [
    'query' => 'orderInformation.amountDetails.totalAmount:100.00',
    'name' => 'Amount Search',
    'limit' => 5,
];

try {
    $result = Cybersource::searchTransactions($amountSearch);
    echo 'Found '.$result['totalCount']." transactions with amount 100.00.\n";
} catch (Exception $e) {
    echo 'Error searching transactions: '.$e->getMessage()."\n";
}
