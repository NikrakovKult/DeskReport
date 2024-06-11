<?php
$conn = mysqli_connect("localhost", "root", "", "DeskReport");

if (!$conn) {
  die("Connection failed: ". mysqli_connect_error());
}

$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'Новая' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
$newData = [];
while ($row = mysqli_fetch_assoc($result)) {
  $newData[] = [
    'date' => $row['Date_by'],
    'count' => $row['count']
  ];
}

$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'В работе' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
$inWorkData = [];
while ($row = mysqli_fetch_assoc($result)) {
  $inWorkData[] = [
    'date' => $row['Date_by'],
    'count' => $row['count']
  ];
}

$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'Приостановлено' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
$pausedData = [];
while ($row = mysqli_fetch_assoc($result)) {
  $pausedData[] = [
    'date' => $row['Date_by'],
    'count' => $row['count']
  ];
}

$query = "SELECT Date_by, COUNT(*) as count FROM orders WHERE Status = 'Завершено' AND Date_by BETWEEN '$monday' AND '$sunday' GROUP BY Date_by";
$result = mysqli_query($conn, $query);
$completedData = [];
while ($row = mysqli_fetch_assoc($result)) {
  $completedData[] = [
    'date' => $row['Date_by'],
    'count' => $row['count']
  ];
}
?>


<html>
  <head>
    <title>Графики заявок</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <h1>Графики заявок</h1>
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
      <div style="width: 45%;">
        <h2>Новые заявки</h2>
        <canvas id="new-chart"></canvas>
      </div>
      <div style="width: 45%;">
        <h2>Заявки в работе</h2>
        <canvas id="in-work-chart"></canvas>
      </div>
      <div style="width: 45%;">
        <h2>Приостановленные заявки</h2>
        <canvas id="paused-chart"></canvas>
      </div>
      <div style="width: 45%;">
        <h2>Завершенные заявки</h2>
        <canvas id="completed-chart"></canvas>
      </div>
    </div>
    <script>
      const ctxNew = document.getElementById('new-chart').getContext('2d');
      const chartNew = new Chart(ctxNew, {
        type: 'line',
        data: {
          labels: <?= json_encode(array_column($newData, 'date'))?>,
          datasets: [{
            label: 'Количество заявок',
            data: <?= json_encode(array_column($newData, 'count'))?>,
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderColor: 'rgba(255, 255, 255, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });

      const ctxInWork = document.getElementById('in-work-chart').getContext('2d');
      const chartInWork = new Chart(ctxInWork, {
        type: 'line',
        data: {
          labels: <?= json_encode(array_column($inWorkData, 'date'))?>,
          datasets: [{
            label: 'Количество заявок',
            data: <?= json_encode(array_column($inWorkData, 'count'))?>,
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderColor: 'rgba(255, 255, 255, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
      const ctxPaused = document.getElementById('paused-chart').getContext('2d');
      const chartPaused = new Chart(ctxPaused, {
        type: 'line',
        data: {
          labels: <?= json_encode(array_column($pausedData, 'date'))?>,
          datasets: [{
            label: 'Количество заявок',
            data: <?= json_encode(array_column($pausedData, 'count'))?>,
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderColor: 'rgba(255, 255, 255, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      }); 

      const ctxCompleted = document.getElementById('completed-chart').getContext('2d');
      const chartCompleted = new Chart(ctxCompleted, {
        type: 'line',
        data: {
          labels: <?= json_encode(array_column($completedData, 'date'))?>,
          datasets: [{
            label: 'Количество заявок',
            data: <?= json_encode(array_column($completedData, 'count'))?>,
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderColor: 'rgba(255, 255, 255, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      }); 
    </script>
    <style>
      body {
        color: white;
        background-color: #000;
      }
      canvas {
        background-color: #000;
      }
    </style>
  </body>
</html>