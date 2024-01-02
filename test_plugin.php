<?php
ob_start();
require_once('includes/db_config.php');
include 'form.php';

/*
 * Plugin Name: test plugin
 * Plugin URI: test.com/plugin
 * Description: <div>plugin for test</div>
 * Author Name: Elham Qafouri
 * Version: 1.0
 * Author URI: elham.qafouri.91@gmail.com
 */


try {
    $required_db_name = 'externaldb';

    // اتصال به دیتابیس با استفاده از PDO
    $pdo = new PDO("mysql:host={$host}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ایجاد دیتابیس اگر وجود نداشته باشد
    $createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS {$required_db_name} DEFAULT CHARACTER SET utf8";
    $pdo->exec($createDatabaseQuery);

    // انتخاب دیتابیس
    $pdo->exec("USE {$required_db_name}");

// کد SQL برای ایجاد جدول
    $sql = "CREATE TABLE IF NOT EXISTS categories (" .
        "id mediumint(9) NOT NULL AUTO_INCREMENT, " .
        "category_name varchar(50) NOT NULL, " .
        "background_color text NOT NULL, " .
        "icon text NOT NULL, " .
        "PRIMARY KEY (id) " .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";


    // اجرای کد SQL
    $pdo->exec($sql);


    // بررسی فرم دسته بندی
    if (isset($_POST["save_category"])) {
        // دریافت داده‌ها از فرم
        $category_name = isset($_POST["category_name"]) ? $_POST["category_name"] : null;
        $background_color = isset($_POST["background_color"]) ? $_POST["background_color"] : null;
        $icon = isset($_POST["icon"]) ? $_POST["icon"] : null;

        // چک کردن وجود مقدار برای category_name
        if ($category_name !== null) {
            // تهیه کد SQL برای درج داده‌ها در جدول categories
            $sql_category = "INSERT INTO categories (category_name, background_color, icon) VALUES (:category_name, :background_color, :icon)";
            $stmt_category = $pdo->prepare($sql_category);
            $stmt_category->bindParam(':category_name', $category_name, PDO::PARAM_STR);
            $stmt_category->bindParam(':background_color', $background_color, PDO::PARAM_STR);
            $stmt_category->bindParam(':icon', $icon, PDO::PARAM_STR);
            $stmt_category->execute();

            // دریافت آخرین id اضافه شده
            $last_category_id = $pdo->lastInsertId();

            // چک کردن وجود مقدار برای subcategory_title
            $subcategory_title = isset($_POST["subcategory_title"]) ? $_POST["subcategory_title"] : null;
            if ($subcategory_title !== null) {
                // تهیه کد SQL برای درج داده‌ها در جدول subcategories
                $sql_subcategory = "INSERT INTO subcategories (subcategory_title, category_id) VALUES (:subcategory_title, :category_id)";
                $stmt_subcategory = $pdo->prepare($sql_subcategory);
                $stmt_subcategory->bindParam(':subcategory_title', $subcategory_title, PDO::PARAM_STR);
                $stmt_subcategory->bindParam(':category_id', $last_category_id, PDO::PARAM_INT);
                $stmt_subcategory->execute();
            }
            // پیام موفقیت آمیز
            echo "Category Data inserted successfully! please wait...";
            // ریدایرکت به همان صفحه پس از 5 ثانیه
            header("refresh:3;url=form.php");
            exit();
        } else {
            // در صورت عدم وجود مقدار برای category_name
            echo "Error: 'category_name' cannot be null.";
        }
    }


    // ایجاد جدول subcategories
    $createSubcategoriesTable = "
        CREATE TABLE IF NOT EXISTS subcategories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            subcategory_title VARCHAR(255),
            category_id MEDIUMINT DEFAULT NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )";
    $pdo->exec($createSubcategoriesTable);


    // بررسی فرم زیر دسته
    if (isset($_POST["save_subcategory"])) {
        // دریافت داده‌ها از فرم
        $subcategory_title = isset($_POST["subcategory_title"]) ? $_POST["subcategory_title"] : null;
        $category_id = isset($_POST["category_id"]) ? $_POST["category_id"] : null; // دریافت آیدی دسته بندی از فرم

        // چک کردن وجود مقدار برای subcategory_title
        if ($subcategory_title !== null) {
            // کد ذخیره داده‌ها در جدول subcategories
            $sql_subcategory = "INSERT INTO subcategories (subcategory_title, category_id) VALUES (:subcategory_title, :category_id)";
            $stmt_subcategory = $pdo->prepare($sql_subcategory);
            $stmt_subcategory->bindParam(':subcategory_title', $subcategory_title, PDO::PARAM_STR);
            $stmt_subcategory->bindParam(':category_id', $category_id, PDO::PARAM_INT); // اضافه کردن آیدی دسته بندی به کوئری
            $stmt_subcategory->execute();

            // پیام موفقیت آمیز
            echo "Subcategory Data inserted successfully! please wait...";
            // ریدایرکت به همان صفحه پس از 5 ثانیه
            header("refresh:3;url=form.php");
            exit();
        } else {
            // در صورت عدم وجود مقدار برای subcategory_title
            echo "Error: 'subcategory_title' cannot be null.";
        }
    }


} catch (PDOException $e) {
    // در صورت وجود خطا، پیام خطا را نشان می‌دهد
    $error_message = "DB ERROR: " . $e->getMessage();
    echo $error_message;
}


////////////////////


?>
    <div class="form-container">
        <form method="post" action="test_plugin.php" style="margin-left: 10%">
            <input type="text" name="subcategory_title" placeholder="افزودن عنوان">
            <select name="category_id">
                <option selected disabled>انتخاب زیر دسته</option>
                <?php
                // اینجا باید کوئری انتخاب برای خواندن اطلاعات از جدول categories را اجرا کنید و گزینه‌های dropdown را پر کنید
                $select_categories_query = "SELECT id, category_name FROM categories";
                $categories_result = $pdo->query($select_categories_query);
                while ($row = $categories_result->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $row['id'] . "'>" . $row['category_name'] . "</option>";
                }
                ?>
            </select>
            <input type="submit" name="save_subcategory" value="ذخیره زیر دسته">
        </form>
    </div>

<?php


// کوئری انتخاب برای خواندن اطلاعات از دیتابیس
$select_query = "SELECT category_name, background_color, icon FROM categories";

// اجرای کوئری
$result = $pdo->query($select_query);

echo "<select name='selected_category' style='margin-left: 10%'>";
echo "<option selected value='دسته بندی'> دسته بندی ها</option>";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<option style='background-color: " . $row['background_color'] . ";' value='" . $row['category_name'] . "'>" . $row['category_name'] . " - " . $row['icon'] . "</option>";
}
echo "</select><br>";







