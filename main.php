#! /usr/bin/env php

<?php

class TaskTracker {
  private $prompt = "task-cli ";

  public function run() {
    $this->initConfigs();
    while (1) {
      $line = readline($this->prompt);

      if ($line) {
        $line = trim($line);
        readline_add_history($line);
        $commands = explode(" ", $line);
        $this->process_command($commands);
      }
    }
  }

  private function addTask($task) {
    $tasks = $this->getAllTasks();
    if (sizeof($tasks) === 0) {
      $id = 0;
    } else {
      $id = (int) ($tasks["lastId"] + 1);
    }
    array_push($tasks["tasks"], ["id" => $id, "task" => $task, "status" => "not done"]);
    $tasks["lastId"] = $id;
    $content = json_encode($tasks, JSON_PRETTY_PRINT);
    file_put_contents("db.json", $content);
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

  public function add_command($command) {
    $i = 1;
    $j = 0;
    $k = 0;
    $argument = $command[$i];
    $task = "";

    if ($argument[$j] !== '"'){
      echo "argument must be in quotes: add \"some argument\"\n";
      return ;
    } else {
      $j++;
      while (isset($argument[$j]) && $argument[$j] !== "\"")
        $task[$k++] = $argument[$j++];

      if (!isset($argument[$j])){
        while (isset($command[++$i])) {
          $j = 0;
          $argument = $command[$i];
          $task[$k++] = " ";

          while (isset($argument[$j]) && $argument[$j] !== "\"")
            $task[$k++] = $argument[$j++];
        }
      }
      if (!isset($argument[$j]) || $argument[$j] !== "\""){
        echo "Explect close quotes: add \"some argument\"\n";
        return ;
      }
    }

    $this->addTask($task);
  }
  public function update_command($command) {
    echo "update command \n";
  }
  public function delete_command($command) {
    if (!isset($command[1])) {
      echo "task id is required\n";
      return ;
    }

    $data = $this->getAllTasks();
    foreach($data["tasks"] as $index => $task) {
      if ($task["id"] == $command[1]){
        unset($data["tasks"][$index]);
        $data["tasks"] = array_values($data["tasks"]);
        file_put_contents("db.json", json_encode($data, JSON_PRETTY_PRINT));
        break;
      }
    }

    echo "Success!\n";
  }
  public function markInProgress_command($command) {
    if (!isset($command[1])) {
      echo "task id is required\n";
      return ;
    }

    $tasksRaw = $this->getAllTasks();
    foreach($tasksRaw["tasks"] as &$task) {
      if ($task["id"] == $command[1]) {
        $task["status"] = "in progress";
        file_put_contents("db.json", json_encode($tasksRaw, JSON_PRETTY_PRINT));
        return ;
      }
    }

    echo "task id not found\n";
  }
  public function listDone_command() {
    $this->printTaks("done");
  }
  public function listTodo_command() {
    $this->printTaks("not done");
  }
  public function listInProgress_command() {
    $this->printTaks("in progress");
  }
  public function list_command() {
    $this->printTaks();
  }
  public function markDone_command($command) {
    if (!isset($command[1])) {
      echo "task id is required\n";
      return ;
    }

    $tasksRaw = $this->getAllTasks();
    foreach($tasksRaw["tasks"] as &$task) {
      if ($task["id"] == $command[1]) {
        $task["status"] = "done";
        file_put_contents("db.json", json_encode($tasksRaw, JSON_PRETTY_PRINT));
        return ;
      }
    }


    echo "task id not found\n";
  }

  public function exit_command() {
    exit(0);
  }

  private function process_command($command) {
    switch($command[0]) {
      case "add" :
        $this->add_command($command);
        break;
      case "update" :
        $this->update_command($command);
        break;
      case "delete" :
        $this->delete_command($command);
        break;
      case "mark-in-progress" :
        $this->markInProgress_command($command);
        break;
      case "mark-done" :
        $this->markDone_command($command);
        break;
      case "list":
        if (isset($command[1])) {
          switch($command[1]) {
            case "done":
              $this->listDone_command();
              break;
            case "todo":
              $this->listTodo_command();
              break;
            case "in-progress":
              $this->listInProgress_command();
              break;
            default:
              echo "$command[1] is invalid argument\n";
              return ;
          }
        } else {
          $this->list_command();
        }
        break;
      case "exit":
        $this->exit_command();
        break;
      default:
        echo "command '$command[0]' not found!\n";
    }
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