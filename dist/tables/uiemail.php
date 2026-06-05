<?php
// Default values for previewing in browser
if (!isset($product)) {
    $product = [
        'name' => "Premium Wireless Headphones",
        'price' => 12999.00,
        'stock' => 15,
        'status' => 'active',
        'created_date' => date('Y-m-d')
    ];
}
$dashboard_url = isset($dashboard_url) ? $dashboard_url : "#";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Product Notification</title>
    <style>
      
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 5px 10px 30px rgba(0,0,0,0.05);
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .product-card {
            background-color: #dde8f3;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #dde8f3;
            text-align: center;
        }
        .product-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }
        .product-name {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .price-large {
            font-size: 32px;
            font-weight: 800;
            color: #28a745;
            display: block;
            margin: 10px 0;
        }
        .details-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }
        .details-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .text-success { color: #28a745; }
        .text-warning { color: #ffc107; }
        .text-danger { color: #dc3545; }
        .text-muted { color: #6c757d; }
        
        .badge-status {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
            margin-top: 15px;
        }
        .bg-success-subtle { background-color: #d1e7dd; color: #0f5132; }
        .bg-danger-subtle { background-color: #f8d7da; color: #842029; }

        .footer {
            background-color: #f8f9fa;
            padding: 25px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .btn {
            display: inline-block;
            padding: 14px 30px;
            background-color: #28a745;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Added New Product!</h1>
        </div>
        <div class="content">
            <p>Hello Admin,</p>
            <p>A new product has been successfully added to your catalog. Here are the full details:</p>
            
            <div class="product-card">
                <span class="product-label">Product Name</span>
                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>

                <span class="product-label">Retail Price</span>
                <div class="price-large">₹<?php echo number_format((float)$product['price'], 2); ?></div>

                <div class="details-grid">
                    <div class="details-col" style="border-right: 1px solid #e9ecef;">
                        <span class="product-label">Current Stock</span>
                        <div style="font-weight: 700; font-size: 18px;" class="<?php echo $product['stock'] > 10 ? 'text-success' : ($product['stock'] > 0 ? 'text-warning' : 'text-danger'); ?>">
                            <?php echo $product['stock'] > 0 ? $product['stock'] . ' Units' : 'Out of Stock'; ?>
                        </div>
                    </div>
                    <div class="details-col">
                        <span class="product-label">Added Date</span>
                        <div style="font-weight: 700; font-size: 18px;">
                            <?php echo date('M d, Y', strtotime($product['created_date'])); ?>
                        </div>
                    </div>
                </div>

                <div class="badge-status <?php echo strtolower($product['status']) === 'active' ? 'bg-success-subtle' : 'bg-danger-subtle'; ?>">
                    Status: <?php echo htmlspecialchars($product['status']); ?>
                </div>
            </div>

            <p style="text-align: center;">Click the button below to view and manage this product in your admin dashboard.</p>
            
        </div>
        
    </div>
</body>
</html>