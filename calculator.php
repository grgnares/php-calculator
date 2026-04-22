<?php
session_start();

$result = null;
$error = "";

$a = "";
$b = "";
$op = "add";

// current page view
$view = $_GET["view"] ?? "calculator";
$allowedViews = ["calculator", "history"];
if (!in_array($view, $allowedViews, true)) {
    $view = "calculator";
}

// Create history storage if not exists
if (!isset($_SESSION["history"])) {
    $_SESSION["history"] = [];
}

// Clear history
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["clear_history"])) {
    $_SESSION["history"] = [];
    header("Location: ?view=history");
    exit;
}

// Calculate
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["calculate"])) {
    $a = $_POST["a"] ?? "";
    $b = $_POST["b"] ?? "";
    $op = $_POST["op"] ?? "add";

    $allowedOps = ["add", "subtract", "multiply", "divide"];

    if (!in_array($op, $allowedOps, true)) {
        $error = "Invalid operation selected.";
    } elseif ($a === "" || $b === "") {
        $error = "Both numbers are required.";
    } elseif (!is_numeric($a) || !is_numeric($b)) {
        $error = "Please enter valid numeric values.";
    } else {
        $num1 = (float)$a;
        $num2 = (float)$b;

        switch ($op) {
            case "add":
                $result = $num1 + $num2;
                $symbol = "+";
                break;
            case "subtract":
                $result = $num1 - $num2;
                $symbol = "-";
                break;
            case "multiply":
                $result = $num1 * $num2;
                $symbol = "×";
                break;
            case "divide":
                $symbol = "÷";
                    $result = $num1 / $num2;
                break;
        }

        if ($error === "" && $result !== null) {
            $_SESSION["history"][] = [
                "expression" => $num1 . " " . $symbol . " " . $num2 . " = " . $result,
                "time" => date("Y-m-d H:i:s")
            ];

            if (count($_SESSION["history"]) > 5) {
                array_shift($_SESSION["history"]);
            }
        }
    }

    $view = "calculator";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Calculator</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .navbar {
            background: #1f2430;
            padding: 14px 24px;
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .navbar a {
            color: #d7dde8;
            text-decoration: none;
            font-size: 18px;
            padding-bottom: 4px;
        }

        .navbar a.active {
            color: #ffffff;
            border-bottom: 2px solid #4da3ff;
        }

        .wrapper {
            max-width: 500px;
            margin: 40px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 15px;
            background: #2d89ef;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.95;
        }

        .result {
            margin-top: 18px;
            padding: 12px;
            background: #eafaf1;
            color: #1f7a3f;
            border-radius: 6px;
            font-weight: bold;
        }

        .error {
            margin-top: 18px;
            padding: 12px;
            background: #fdecec;
            color: #c0392b;
            border-radius: 6px;
            font-weight: bold;
        }

        .history-item {
            padding: 12px 0;
            border-bottom: 1px solid #e3e3e3;
        }

        .history-time {
            color: #666;
            font-size: 12px;
            margin-top: 4px;
        }

        .clear-btn {
            background: #d9534f;
            margin-top: 20px;
        }

        .empty {
            color: #777;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="?view=calculator" class="<?php echo $view === 'calculator' ? 'active' : ''; ?>">Calculator</a>
        <a href="?view=history" class="<?php echo $view === 'history' ? 'active' : ''; ?>">History</a>
    </div>

    <div class="wrapper">
        <?php if ($view === "calculator"): ?>
            <h2>Calculator</h2>

            <form method="post" action="?view=calculator">
                <label>First Number</label>
                <input type="text" name="a" value="<?php echo $a; ?>">

                <label>Second Number</label>
                <input type="text" name="b" value="<?php echo $b; ?>">

                <label>Operation</label>
                <select name="op">
                    <option value="add" <?php if ($op === "add") echo "selected"; ?>>Add</option>
                    <option value="subtract" <?php if ($op === "subtract") echo "selected"; ?>>Subtract</option>
                    <option value="multiply" <?php if ($op === "multiply") echo "selected"; ?>>Multiply</option>
                    <option value="divide" <?php if ($op === "divide") echo "selected"; ?>>Divide</option>
                </select>

                <button type="submit" name="calculate">Calculate</button>
            </form>

            <?php if ($result !== null): ?>
                <div class="result">Result: <?php echo $result; ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

        <?php elseif ($view === "history"): ?>
            <h2>Calculation History</h2>

            <?php if (!empty($_SESSION["history"])): ?>
                <?php foreach (array_reverse($_SESSION["history"]) as $item): ?>
                    <div class="history-item">
                        <div><?php echo $item["expression"]; ?></div>
                        <div class="history-time"><?php echo $item["time"]; ?></div>
                    </div>
                <?php endforeach; ?>

                <form method="post" action="?view=history">
                    <input type="hidden" name="clear_history" value="1">
                    <button type="submit" class="clear-btn">Clear History</button>
                </form>
            <?php else: ?>
                <p class="empty">No calculations yet.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</body>
</html>
