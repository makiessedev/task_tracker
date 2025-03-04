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
        $line = trim($line);
        readline_add_history($line);
        $commands = explode(" ", $line);

        $isValidCommand = $this->validateCommand($commands);
        if (!$isValidCommand)
          echo "-- invalid command or arguments!\n";
        else {
          switch($commands[0]) {
            case "add" :
            case "update" :
            case "delete" :
            case "mark-in-progress" :
            case "mark-done" :
              break;
            case "list":
              if (!isset($commands[1]))
                $this->printTaks();
              else {
                switch($commands[1]) {
                  case "done" :
                    $this->printTaks("done");
                    break;
                  case "todo" :
                    $this->printTaks("not done");
                    break;
                  case "in-progress" :
                    $this->printTaks("in progress");
                    break;
                  default:
                    echo "Error on list tasks";
                }
              }
              break;
            case "exit":
              echo "other\n";
            default:
              echo "Error on verify command";
          }
        }
      }
    }
  }

  private function getAllTasks() {
    return (json_decode(file_get_contents("db.json"), true));
  }

  private function printTask($task) {
    echo "[$task[id]] - $task[task] - $task[status]\n";
  }

  private function printTaks($delimiter = null) {
    $content = $this->getAllTasks();
    foreach ($content["tasks"] as $task) {
      if (isset($delimiter))
        $delimiter === $task["status"] ? $this->printTask($task) : null;
      else
      $this->printTask($task);
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

    if ($command[0] == "list" && isset($command[1]) && sizeof($command) === 2) {
      switch ($command[1]) {
        case "done":
        case "todo":
        case "in-progress":
          return (true);
        default:
          return (false);
      }
    }

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
      ["cmd" => "list done", "args_length" => 2],
      ["cmd" => "list todo", "args_length" => 2],
      ["cmd" => "list in-progress", "args_length" => 2],
      ["cmd" => "exit", "args_length" => 1],
    ];
  }
}

new TaskTracker()->run();