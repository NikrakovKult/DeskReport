$(document).ready(function() {
    // Функция для загрузки данных из таблицы orders
    function loadOrders(status) {
      $.ajax({
        type: "POST",
        url: "load_orders.php",
        data: {status: status},
        dataType: "html",
        success: function(data) {
          $(".main-table").html(data);
        }
      });
    }
  
    // Вызов функции loadOrders при загрузке страницы
    loadOrders('');
  
    // Вызов функции loadOrders при клике на навигационном меню
    $(".nav-item").on("click", function() {
      loadOrders('');
    });
  

    
    // Функция для поиска по таблице orders
    $('#search-input').on('keypress', function(e) {
        if (e.which === 13) { // 13 - код кнопки Enter
            var searchQuery = $(this).val();
            var status = $('#status').val(); // Добавляем значение фильтра по статусам
            $.ajax({
                type: "POST",
                url: "search_orders.php",
                data: {searchQuery: searchQuery, status: status},
                dataType: "html",
                success: function(data) {
                    $(".main-table").html(data);
                }
            });
        }
    });
    $('#status').on('change', function() {
    var status = $(this).val();
    var searchQuery = $('#search-input').val();
    $.ajax({
        type: "POST",
        url: "search_orders.php",
        data: {searchQuery: searchQuery, status: status},
        dataType: "html",
        success: function(data) {
            $(".main-table").html(data);
        }
    });
});
    // Функция для загрузки данных из таблицы actives
    $('#actives-btn').on('click', function() {
        $.ajax({
            type: "POST",
            url: "<?php echo $_SERVER['PHP_SELF'];?>",
            data: {showActives: true},
            dataType: "html",
            success: function(data) {
                $(".main-table").html(data);
            }
        });
    });
});
function showActivesTable() {
    document.getElementById("orders-table").style.display = "none";
    document.getElementById("actives-table").style.display = "table";
}
function showOrdersTable() {
    document.getElementById("orders-table").style.display = "table";
    document.getElementById("actives-table").style.display = "none";
}