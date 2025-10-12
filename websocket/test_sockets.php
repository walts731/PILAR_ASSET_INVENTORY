<?php
// Save this as test_sockets.php
if (extension_loaded('sockets')) {
    echo "Sockets extension is loaded!";
} else {
    echo "Sockets extension is NOT loaded!";
}