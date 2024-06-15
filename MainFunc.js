$(document).ready(function () {
    // Функция для загрузки данных из таблицы orders
    function loadOrders(status, specialist) {
        $.ajax({
            type: "POST",
            url: "load_orders.php",
            data: {
                status: status,
                specialist: specialist,
                username: '<?php echo $user_data["username"]; ?>' // добавляем username в данные
            },
            dataType: "html",
            success: function (data) {
                $(".main-table").html(data);
            }
        });
    }

    // Вызов функции loadOrders при загрузке страницы
    loadOrders('');
    $('#specialist').on('change', function () {
        var specialist = $(this).val();
        var status = $('#status').val();
        loadOrders(status, specialist);
    });
    // Вызов функции loadOrders при клике на навигационном меню
    $(".nav-item").on("click", function () {
        loadOrders('');
    });



    // Функция для поиска по таблице orders
    $('#search-input').on('keypress', function (e) {
        if (e.which === 13) { // 13 - код кнопки Enter
            var searchQuery = $(this).val();
            var status = $('#status').val(); // Добавляем значение фильтра по статусам
            $.ajax({
                type: "POST",
                url: "search_orders.php",
                data: { searchQuery: searchQuery, status: status },
                dataType: "html",
                success: function (data) {
                    $(".main-table").html(data);
                }
            });
        }
    });
    $('#status').on('change', function () {
        var status = $(this).val();
        var searchQuery = $('#search-input').val();
        $.ajax({
            type: "POST",
            url: "search_orders.php",
            data: { searchQuery: searchQuery, status: status },
            dataType: "html",
            success: function (data) {
                $(".main-table").html(data);
            }
        });
    });
    // Функция для загрузки данных из таблицы actives
    $('#actives-btn').on('click', function () {
        $.ajax({
            type: "POST",
            url: "<?php echo $_SERVER['PHP_SELF'];?>",
            data: { showActives: true },
            dataType: "html",
            success: function (data) {
                $(".main-table").html(data);
            }
        });
    });

});

function showActivesTable() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "table";
    document.getElementsByClassName("graph")[0].style.display = "none";
    document.getElementsByClassName("notes")[0].style.display = "notes";
}
function showOrdersTable() {
    document.getElementById("orders-table").style.display = "table";
    document.getElementById("actives-table").style.display = "none";
    document.getElementsByClassName("graph")[0].style.display = "none";
    document.getElementsByClassName("notes")[0].style.display = "none";
}
function showGraph() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "none";
    document.getElementsByClassName("graph")[0].style.display = "flex";
    document.getElementsByClassName("notes")[0].style.display = "none";
}
function showZametki() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "none";
    document.getElementsByClassName("graph")[0].style.display = "none";
    document.getElementsByClassName("notes")[0].style.display = "block";
}

$(document).ready(function() {
    $('#update-button').on('click', function() {
        $.ajax({
            type: 'POST',
            url: 'search_orders.php',
            data: { update: true }, // добавляем параметр update
            dataType: 'html',
            success: function(data) {
              $('#orders-table').html(data); // обновляем содержимое таблицы
            }
          });
    });
  });
  $(document).ready(function() {
    let updateIntervalId = null;
    const updateIntervalSelect = document.getElementById('update-interval');

    updateIntervalSelect.addEventListener('change', () => {
        const intervalValue = updateIntervalSelect.value;
        if (intervalValue > 0) {
            if (updateIntervalId) {
                clearInterval(updateIntervalId);
            }
            updateIntervalId = setInterval(updateOrdersTable, intervalValue * 1000);
        } else {
            clearInterval(updateIntervalId);
            updateIntervalId = null;
        }
    });

    function updateOrdersTable() {
        $.ajax({
            type: 'POST',
            url: 'search_orders.php',
            data: { update: true }, // добавляем параметр update
            dataType: 'html',
            success: function(data) {
                $('#orders-table').html(data); // обновляем содержимое таблицы
            }
        });
    }
});

 