<?php
session_start();
session_unset();
session_destroy();

header("Location:../../index.html?sukses=logout_success");
exit;
