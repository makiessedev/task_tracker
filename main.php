#! /usr/bin/env php

<?php

class TaskTracker {
  private $prompt = "task-cli ";
  private $dbName = "db.json";

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
    array_push($tasks["tasks"], [
      "id" => $id,
      "description" => $task,
      "status" => "not done",
      "createdAt" => date("Y-m-d H:i:s"),
      "updatedAt" => date("Y-m-d H:i:s")
    ]);
    $tasks["lastId"] = $id;
    $content = json_encode($tasks, JSON_PRETTY_PRINT);
    file_put_contents($this->dbName, $content);
  }

  private function getAllTasks() {
    return (json_decode(file_get_contents($this->dbName), true));
  }

  private function printTask($task) {
    echo "[$task[id]] - $task[description] - $task[status] - $task[createdAt] - $task[updatedAt]\n";
  }

  private function printTaks($delimiter = null) {
    $content = $this->getAllTasks();
    echo "[id] - [description] - [status] - [createdAt] - [updatedAt]\n";
    foreach ($content["tasks"] as $task) {
      if (isset($delimiter))
        $delimiter === $task["status"] ? $this->printTask($task) : null;
      else
        $this->printTask($task);
    }
  }

  private function initConfigs() {
    $fileStruct = [
      "lastId" => 0,
      "tasks" => []
    ];

    if (!file_exists($this->dbName)) {
      file_put_contents($this->dbName, json_encode($fileStruct));
    }

    if (!function_exists("pcntl_signal"))
      exit(0);
    pcntl_signal(SIGINT, function() {
      echo "\n";
    });

    date_default_timezone_set("Africa/Luanda");
  }

  private function get_task($command, $start) {
    $j = 0;
    $k = 0;
    $argument = $command[$start];
    $task = "";

    if ($argument[$j] !== '"'){
      echo "argument must be in quotes: command <id> \"some argument\"\n";
      return ;
    } else {
      $j++;
      while (isset($argument[$j]) && $argument[$j] !== "\"")
        $task[$k++] = $argument[$j++];

      if (!isset($argument[$j])){
        while (isset($command[++$start])) {
          $j = 0;
          $argument = $command[$start];
          $task[$k++] = " ";

          while (isset($argument[$j]) && $argument[$j] !== "\"")
            $task[$k++] = $argument[$j++];
        }
      }
      if (!isset($argument[$j]) || $argument[$j] !== "\""){
        echo "Explect close quotes: command <id> \"some argument\"\n";
        return ;
      }
    }
    return ($task);
  }

  public function add_command($command) {
    $task = $this->get_task($command, 1);

    if (isset($task)){
      $this->addTask($task);
      echo "Task added successfully (ID: " . $this->getAllTasks()["lastId"] . ")\n";
    }
  }
  public function update_command($command) {
    if (!isset($command[1]) || !isset($command[2])) {
      echo "Invalid sintaxe!\n";
      return ;
    }

    $newValue = $this->get_task($command, 2);

    if (!isset($newValue))
      return ;

    $tasksRaw = $this->getAllTasks();
    foreach($tasksRaw["tasks"] as &$task) {
      if ($task["id"] == $command[1]) {
        $task["description"] = $newValue;
        $task["updatedAt"] = date("Y-m-d H:i:s");;
        file_put_contents($this->dbName, json_encode($tasksRaw, JSON_PRETTY_PRINT));
        return ;
      }
    }

    echo "Success!\n";
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
        file_put_contents($this->dbName, json_encode($data, JSON_PRETTY_PRINT));
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
        $task["updatedAt"] = date("Y-m-d H:i:s");
        file_put_contents($this->dbName, json_encode($tasksRaw, JSON_PRETTY_PRINT));
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
        $task["updatedAt"] = date("Y-m-d H:i:s");
        file_put_contents($this->dbName, json_encode($tasksRaw, JSON_PRETTY_PRINT));
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
}

new TaskTracker()->run();