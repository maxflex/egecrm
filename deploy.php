<?php
/*
 * This file has been generated automatically.
 * Please change the configuration for correct use deploy.
 */

require 'recipe/common.php';

// Set configurations
set('repository', 'https://github.com/maxflex/egecrm.git');
set('shared_dirs', ['extentions']);
set('shared_files', ['config.php', 'favicon.png']);
set('writable_dirs', ['files']);

// Configure servers
server('production', 'lk.ege-centr.ru')
    ->user('root')
    ->password('Stu2Udre')
    ->env('deploy_path', '/home/egecrm');

/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:symlink',
    'cleanup',
])->desc('Deploy your project');

after('deploy', 'success');