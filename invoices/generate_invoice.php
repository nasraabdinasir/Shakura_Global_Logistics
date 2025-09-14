<?php  
require_once '../includes/auth.php';
requireRole(['admin','staff','customer']);
include '../includes/db.php';

if (isset($_GET['parcel_id'])) {
    $parcel_id = intval($_GET['parcel_id']);

    // Check if invoice already exists for this parcel
    $invoice = $conn->query("SELECT * FROM invoices WHERE parcel_id = $parcel_id")->fetch_assoc();

    if (!$invoice) {
        $p = $conn->query("SELECT * FROM parcels WHERE id=$parcel_id")->fetch_assoc();
        $sender = $conn->query("SELECT * FROM users WHERE id=".$p['sender_id'])->fetch_assoc();
        $invoice_no = "INV".str_pad($parcel_id,5,'0',STR_PAD_LEFT); // e.g., INV00027
        $amount = $p['price'];
        $conn->query("INSERT INTO invoices (parcel_id, invoice_number, amount) VALUES ('$parcel_id','$invoice_no','$amount')");
        $invoice = $conn->query("SELECT * FROM invoices WHERE parcel_id = $parcel_id")->fetch_assoc();
    } else {
        $p = $conn->query("SELECT * FROM parcels WHERE id=".$invoice['parcel_id'])->fetch_assoc();
        $sender = $conn->query("SELECT * FROM users WHERE id=".$p['sender_id'])->fetch_assoc();
        $invoice_no = $invoice['invoice_number'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Invoice</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #fff; }
        .invoice-container { max-width: 900px; margin: 30px auto; background: #fff; border: 1.5px solid #eee2c2; padding: 35px 40px 35px 40px; font-size: 1.07em; }
        .header-logo { width: 120px; }
        .company-title { color: #a87f51; font-weight: 700; font-size: 2em; }
        .company-address { color: #8a7b5b; }
        .invoice-title { color: #a87f51; font-weight: 600; font-size: 1.38em; }
        .invoice-table thead { background: #f8f1e3; color: #8d6e32; }
        .invoice-table th, .invoice-table td { vertical-align: middle; border: 1px solid #e7dac6; }
        .invoice-table td { background: #fff; }
        .bold { font-weight: 700; }
        .bank-box { background: #f6f0e2; padding: 12px 14px; border-radius: 7px; border:1.5px solid #f2d6ad; font-size:1em; }
        .label { color: #a87f51; font-weight: 600; }
        .border-bottom-strong { border-bottom: 3px solid #e7dac6; }
        .balance-box { font-size: 1.16em; font-weight: bold; color: #a87f51; border-top:2.3px solid #a87f51; }
        .footer-text { color: #8a7b5b; font-size: 0.99em;}
        .invoice-table .rate-cell, .invoice-table .amount-cell { text-align:right; }
        .invoice-table .desc-cell { padding-left: 8px; }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="row">
        <div class="col-7 d-flex align-items-center">
            <img src="../assets/images/logo.jpeg" alt="Logo" class="header-logo me-3">
            <div>
                <div class="company-title">SHAKURA GLOBAL EXPRESS LTD</div>
                <div class="company-address">
                    NAIROBI 00100<br>
                    Kenya<br>
                    +254 757 249 525<br>
                    info@shakuraglobalexpress.com
                </div>
            </div>
        </div>
        <div class="col-5 text-end">
            <span class="invoice-title">TAX INVOICE</span>
        </div>
    </div>

    <div class="row mt-4 mb-2">
        <div class="col-7">
            <div class="label">Bill To</div>
            <div class="bold"><?= htmlspecialchars($sender['full_name']) ?></div>
            <?= htmlspecialchars($sender['address'] ?? '-') ?><br>
            <?= htmlspecialchars($sender['email'] ?? '-') ?><br>
            <?= htmlspecialchars($sender['phone'] ?? '-') ?><br>
            <?= htmlspecialchars($sender['country'] ?? '') ?>
        </div>
        <div class="col-5">
            <table style="width:100%">
                <tr><td class="label">Invoice #:</td><td class="text-end"><?= htmlspecialchars($invoice_no) ?></td></tr>
                <tr><td class="label">Invoice Date:</td><td class="text-end"><?= date("d-m-Y") ?></td></tr>
                <tr><td class="label">Terms:</td><td class="text-end">Due on Receipt</td></tr>
                <tr><td class="label">Due Date:</td><td class="text-end"><?= date("d-m-Y") ?></td></tr>
            </table>
        </div>
    </div>

    <table class="table invoice-table mt-4 mb-1">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Description</th>
                <th style="width:60px;">Qty</th>
                <th style="width:80px;" class="rate-cell">Rate</th>
                <th style="width:100px;" class="amount-cell">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Calculate amounts (in $)
            $qty = round($p['chargeable_weight'],2);
            $rate = isset($p['price_per_kg']) ? round($p['price_per_kg'],2) : 0;
            $airfreight = $qty * $rate;
            $handling = 0.00;
            $transport = 0.00;
            $dg = 0.00;
            $exdoc = 0.00;

            // Fragile logic
            $fragile_is_yes = ($p['fragile'] == 1 || strtolower($p['fragile']) === 'yes');
            $fragile_amt = $fragile_is_yes ? 100.00 : 0.00;
            $fragile_label = $fragile_is_yes ? 'Yes' : 'No';

            $subtotal = $airfreight + $handling + $transport + $dg + $exdoc + $fragile_amt;
            ?>
            <tr>
                <td>1</td>
                <td class="desc-cell">AIR FREIGHT INCLUDING CLEARANCE <?= strtoupper($p['dispatch_origin']) ?>-<?= strtoupper($p['delivery_country']) ?></td>
                <td><?= $qty ?></td>
                <td class="rate-cell"><?= number_format($rate,2) ?></td>
                <td class="amount-cell"><?= number_format($airfreight,2) ?></td>
            </tr>
            <tr>
                <td>2</td>
                <td class="desc-cell">HANDLING</td>
                <td>1.00</td>
                <td class="rate-cell"><?= number_format($handling,2) ?></td>
                <td class="amount-cell"><?= number_format($handling,2) ?></td>
            </tr>
            <tr>
                <td>3</td>
                <td class="desc-cell">TRANSPORT</td>
                <td>1.00</td>
                <td class="rate-cell"><?= number_format($transport,2) ?></td>
                <td class="amount-cell"><?= number_format($transport,2) ?></td>
            </tr>
            <tr>
                <td>4</td>
                <td class="desc-cell">DG</td>
                <td>1.00</td>
                <td class="rate-cell"><?= number_format($dg,2) ?></td>
                <td class="amount-cell"><?= number_format($dg,2) ?></td>
            </tr>
            <tr>
                <td>5</td>
                <td class="desc-cell">EX DOC</td>
                <td>1.00</td>
                <td class="rate-cell"><?= number_format($exdoc,2) ?></td>
                <td class="amount-cell"><?= number_format($exdoc,2) ?></td>
            </tr>
            <tr>
                <td>6</td>
                <td class="desc-cell">FRAGILE (<?= $fragile_label ?><?php if($fragile_amt > 0) echo ', Surcharge +$100'; ?>)</td>
                <td>1.00</td>
                <td class="rate-cell"><?= number_format($fragile_amt,2) ?></td>
                <td class="amount-cell"><?= number_format($fragile_amt,2) ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end bold">Sub Total</td>
                <td class="amount-cell"><?= number_format($subtotal,2) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end bold">Total</td>
                <td class="amount-cell">$<?= number_format($subtotal,2) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end bold">Balance Due</td>
                <td class="amount-cell balance-box">$<?= number_format($subtotal,2) ?></td>
            </tr>
        </tfoot>
    </table>
    <div class="footer-text mt-3 mb-1">
        If you have any questions about this invoice, please contact Ms. Nasra [+254757249525], Email: info@shakuraglobalexpress.com
    </div>
    <div class="row mt-3">
        <div class="col-12 bank-box">
            <span class="bold">Terms & Conditions</span><br>
            Payment should be made to:<br>
            Currency: <b>$</b><br>
            Bank: EQUITY BANK<br>
            Account Name: SHAKURA GLOBAL EXPRESS LTD<br>
            Account No: 0840285418037<br>
            Swift Code: EQBLKENA<br>
            Branch: EASTLEIGH BRANCH
        </div>
    </div>
</div>
</body>
</html>
