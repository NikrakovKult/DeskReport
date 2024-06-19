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
    document.getElementsByClassName("notes")[0].style.display = "none";
    document.getElementById("users-block").style.display = "none";
    document.getElementById("clients-table").style.display = "none";
}
function showOrdersTable() {
    document.getElementById("orders-table").style.display = "table";
    document.getElementById("actives-table").style.display = "none";
    document.getElementsByClassName("graph")[0].style.display = "none";
    document.getElementsByClassName("notes")[0].style.display = "none";
    document.getElementById("users-block").style.display = "none";
    document.getElementById("clients-table").style.display = "none";
}
function showGraph() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "none";
    document.getElementsByClassName("graph")[0].style.display = "flex";
    document.getElementsByClassName("notes")[0].style.display = "none";
    document.getElementById("users-block").style.display = "none";
    document.getElementById("clients-table").style.display = "none";
}
function showZametki() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "none";
    document.getElementsByClassName("graph")[0].style.display = "none";
    document.getElementsByClassName("notes")[0].style.display = "block";
    document.getElementById("users-block").style.display = "none";
    document.getElementById("clients-table").style.display = "none";
}
function showUsers() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "none";
    document.getElementById("users-block").style.display = "flex";
    document.getElementById("clients-table").style.display = "none";
    document.getElementsByClassName("graph")[0].style.display = "none";
    document.getElementsByClassName("notes")[0].style.display = "none";
}
function showClients() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "none";
    document.getElementById("users-block").style.display = "none";
    document.getElementById("clients-table").style.display = "table";
    document.getElementsByClassName("graph")[0].style.display = "none";
    document.getElementsByClassName("notes")[0].style.display = "none";
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

function updateTable(specialist) {
    $.ajax({
        type: "POST",
        url: "search_orders.php",
        data: { specialist: specialist },
        dataType: "html",
        success: function(data) {
            $(".main-table").html(data);
        }
    });
}
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('delete-user-btn')) {
        var userId = event.target.getAttribute('data-user-id');
        fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + userId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    event.target.parentNode.remove();
                } else {
                    alert('Ошибка удаления пользователя');
                }
            });
    }
});