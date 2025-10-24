<?php
$hash = '$2y$10$yOy2XT5DqfOQCb.PrcB.e.0/sZXeJE8ED.E5fxT33TYDxalTn0IBa';
$password = '789';

if (password_verify($password, $hash)) {
    echo "✅ Cocok";
} else {
    echo "❌ Tidak cocok";
}
?>
