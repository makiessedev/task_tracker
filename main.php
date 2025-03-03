#! /usr/bin/env php

<?php

class TaskTracker {
  private $prompt = "task-cli ";

  public function run() {
    $this->initConfigs();
    while (1) {
      $line = readline($this->prompt);

      // temp
      if ($line === "exit")
        exit(0);
      //
      if ($line) {
        readline_add_history($line);
        $commands = explode(" ", $line);

        $isValidCommand = $this->validateCommand($commands);
        if (!$isValidCommand)
          echo "-- invalid command or arguments!\n";
      }
    }
  }

  private function initConfigs() {
    if (!function_exists("pcntl_signal"))
      exit(0);
    pcntl_signal(SIGINT, function() {
      echo "\n";
    });
  }

  private function validateCommand($command) {
    $allCommands = $this->getCommands();
    foreach($allCommands as $commands) {
      if ($commands["cmd"] === $command[0] && $commands["args_length"] === sizeof($command))
        return (true);
    }
    return (false);
  }

  private function getCommands() {
    return [
      ["cmd" => "add", "args_length" => 2],
      ["cmd" => "update", "args_length" => 3],
      ["cmd" => "delete", "args_length" => 2],
      ["cmd" => "mark-in-progress", "args_length" => 2],
      ["cmd" => "mark-done", "args_length" => 2],
      ["cmd" => "list", "args_length" => 1],
      ["cmd" => "list done", "args_length" => 1],
      ["cmd" => "list todo", "args_length" => 1],
      ["cmd" => "list in-progress", "args_length" => 1],
      ["cmd" => "exit", "args_length" => 1],
    ];
  }
}

new TaskTracker()->run();