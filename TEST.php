<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Document</title>
</head>
<body>
<?php
$conn = mysqli_connect("localhost", "root", "", "DeskReport");
$sql = "SELECT * FROM orders";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();


$labels = array();
$data = array();
$statuses = array();
$orders = array();


while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['Date_by'];
    $data[] = $row['id'];
    $statuses[] = $row['Status'];
    $orders[] = $row['Discrip'];
}


$labelsJson = json_encode($labels);
$dataJson = json_encode($data);
$statusesJson = json_encode($statuses);
$ordersJson = json_encode($orders);
?>
<div>
    <canvas id="orders-chart"></canvas>
</div>

<script>
    
    var ctx = document.getElementById('orders-chart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $labelsJson;?>,
            datasets: [{
                label: 'Количество заявок',
                data: <?php echo $dataJson;?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            title: {
                display: true,
                text: 'Статистика заявок'
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    
    var statusFilter = document.getElementById('status');
    var orderFilter = document.getElementById('order');

    statusFilter.addEventListener('change', function() {
        var status = this.value;
        var filteredData = <?php echo $dataJson;?>;
        var filteredLabels = <?php echo $labelsJson;?>;

        
        filteredData = filteredData.filter(function(item, index) {
            return <?php echo $statusesJson;?>[index] === status;
        });
        filteredLabels = filteredLabels.filter(function(item, index) {
            return <?php echo $statusesJson;?>[index] === status;
        });

        // Обновляем график
        chart.data.labels = filteredLabels;
        chart.data.datasets[0].data = filteredData;
        chart.update();
    });

    orderFilter.addEventListener('change', function() {
        var order = this.value;
        var filteredData = <?php echo $dataJson;?>;
        var filteredLabels = <?php echo $labelsJson;?>;

        // Фильтруем данные по заявке
        filteredData = filteredData.filter(function(item, index) {
            return <?php echo $ordersJson;?>[index] === order;
        });
        filteredLabels = filteredLabels.filter(function(item, index) {
            return <?php echo $ordersJson;?>[index] === order;
        });

        // Обновляем график
        chart.data.labels = filteredLabels;
        chart.data.datasets[0].data = filteredData;
        chart.update();
    });
</script>

<!-- Форма для фильтров -->
<form>
    <label>Статус:</label>
    <select id="status">
        <option value="">Все статусы</option>
        <?php
        $statuses = array_unique($statuses);
        foreach ($statuses as $status) {
            echo "<option value='$status'>$status</option>";
        }
        ?>
    </select>

    <label>Заявка:</label>
    <select id="order">
        <option value="">Все заявки</option>
        <?php
        $orders = array_unique($orders);
        foreach ($orders as $order) {
            echo "<option value='$order'>$order</option>";
        }
        ?>
    </select>
</form>
</body>
</html>