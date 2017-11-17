<?php
is_dir('/tmp/filesystem') or mkdir('/tmp/filesystem') && chmod('/tmp/filesystem', 0777);

$base_dir = "../vagrantuploads";

exec("rm -fr $base_dir/*");

exec("mkdir -p $base_dir/ftpdriver $base_dir/sftpdriver $base_dir/s3driver $base_dir/localdriver");

echo PHP_EOL;
