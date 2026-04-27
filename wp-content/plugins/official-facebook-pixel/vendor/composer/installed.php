<?php return array(
    'root' => array(
        'name' => 'facebook/pixel-for-wordpress',
        'pretty_version' => 'dev-release/p5.0.1/publish',
        'version' => 'dev-release/p5.0.1/publish',
        'reference' => '7e452cd858659875d8883283001a1f6f8c8f2213',
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'facebook/pixel-for-wordpress' => array(
            'pretty_version' => 'dev-release/p5.0.1/publish',
            'version' => 'dev-release/p5.0.1/publish',
            'reference' => '7e452cd858659875d8883283001a1f6f8c8f2213',
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'guzzlehttp/guzzle' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'techcrunch/wp-async-task' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '9bdbbf9df4ff5179711bb58b9a2451296f6753dc',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../techcrunch/wp-async-task',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
    ),
);
