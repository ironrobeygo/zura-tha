<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!is_cli()) {
            show_404();
        }

        $this->load->library('migration');
    }

    public function latest(): void
    {
        if ($this->migration->latest() === false) {
            echo $this->migration->error_string() . PHP_EOL;
            return;
        }

        echo "Migrations applied successfully." . PHP_EOL;
    }

    public function version($version): void
    {
        $version = (int) $version;

        if ($this->migration->version($version) === false) {
            echo $this->migration->error_string() . PHP_EOL;
            return;
        }

        echo "Migrated to version {$version}." . PHP_EOL;
    }
}
