<?php

require_once __DIR__.'/../vendor/autoload.php';

use Asciisd\Cybersource\Facades\Cybersource;

// Example: Search for transactions by client reference code
$searchParams = [
    'query' => 'clientReferenceInformation.code:01k19gqb9kbw1zta0n65vfgmrv',
    'name' => 'Order Search',
    'timezone' => 'America/Chicago',
    'offset' => 0,
    'limit' => 50,
    'sort' => 'submitTimeUtc:desc',
];

try {
    $response = Cybersource::searchTransactions($searchParams);

    // Check if we got a valid response object
    if ($response instanceof \Asciisd\Cybersource\Responses\TransactionSearchResponse) {
        echo "Search Results:\n";
        echo "===============\n";
        echo 'Search ID: '.$response->searchId."\n";
        echo 'Total Count: '.$response->totalCount."\n";
        echo 'Count: '.$response->count."\n";
        echo 'Limit: '.$response->limit."\n";
        echo 'Offset: '.$response->offset."\n\n";

        // Get pagination info
        $pagination = $response->getPaginationInfo();
        echo "Pagination Info:\n";
        echo "================\n";
        echo "Page {$pagination['current_page']} of {$pagination['total_pages']}\n";
        echo "Showing {$pagination['from']}-{$pagination['to']} of {$pagination['total']} results\n";
        echo 'Has more: '.($pagination['has_more'] ? 'Yes' : 'No')."\n\n";

        // Get transactions
        $transactions = $response->getTransactions();

        if ($transactions->count() > 0) {
            echo "Transactions:\n";
            echo "=============\n";

            foreach ($transactions as $transaction) {
                echo 'Transaction ID: '.$transaction->id."\n";
                echo 'Submit Time: '.$transaction->submitTimeUtc."\n";
                echo 'Reference Code: '.$transaction->getClientReferenceCode()."\n";
                echo 'Status: '.$transaction->getStatus()."\n";
                echo 'Reason Code: '.$transaction->getReasonCode()."\n";
                echo 'Amount: '.$transaction->getTotalAmount().' '.$transaction->getCurrency()."\n";
                echo 'Payment Method: '.$transaction->getPaymentMethod()."\n";
                echo 'Card: '.$transaction->getMaskedCardNumber()."\n";
                echo 'Customer: '.$transaction->getCustomerEmail()."\n";

                if ($transaction->isApproved()) {
                    echo '✅ APPROVED - Approval Code: '.$transaction->getApprovalCode()."\n";
                } elseif ($transaction->isDeclined()) {
                    echo '❌ DECLINED'."\n";
                } else {
                    echo '⚠️  OTHER STATUS'."\n";
                }

                echo 'Detail Link: '.$transaction->getTransactionDetailLink()."\n";
                echo "---\n";
            }

            // Check for more results
            if ($response->hasMoreResults()) {
                echo "\n🔄 More results available!\n";
                echo 'Next offset: '.$response->getNextOffset()."\n";
            }
        } else {
            echo "No transactions found.\n";
        }
    } else {
        // Handle error response
        echo 'API Error: '.($response['response']['rmsg'] ?? 'Unknown error')."\n";
    }

} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage()."\n";
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
    $response = Cybersource::searchTransactions($dateRangeSearch);

    if ($response instanceof \Asciisd\Cybersource\Responses\TransactionSearchResponse) {
        echo 'Found '.$response->totalCount." transactions in the specified date range.\n";

        // Show summary of statuses
        $statusCounts = [];
        foreach ($response->getTransactions() as $transaction) {
            $status = $transaction->getStatus();
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }

        echo "Status breakdown:\n";
        foreach ($statusCounts as $status => $count) {
            echo "  {$status}: {$count}\n";
        }
    } else {
        echo 'Error: '.($response['response']['rmsg'] ?? 'Unknown error')."\n";
    }
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage()."\n";
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
    $response = Cybersource::searchTransactions($amountSearch);

    if ($response instanceof \Asciisd\Cybersource\Responses\TransactionSearchResponse) {
        echo 'Found '.$response->totalCount." transactions with amount 100.00.\n";

        // Show approved vs declined
        $approved = $response->getTransactions()->filter(fn ($t) => $t->isApproved())->count();
        $declined = $response->getTransactions()->filter(fn ($t) => $t->isDeclined())->count();

        echo "Approved: {$approved}, Declined: {$declined}\n";
    } else {
        echo 'Error: '.($response['response']['rmsg'] ?? 'Unknown error')."\n";
    }
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage()."\n";
}

echo "\n=== Examples Complete ===\n";
