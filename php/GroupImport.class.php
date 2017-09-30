<?php
/* Usage
try {
	$groupImport = new GroupImport(__DIR__ . '/2017.xlsx');
	$groups = $groupImport->getGroups();
	$users = $groupImport->getUsers();
} catch (Exception $e) {
	echo $e -> getMessage();
}
*/
require('spreadsheet/excel_reader2.php');
require('spreadsheet/SpreadsheetReader.php');

class GroupImport
{
	private $data = [];
	private $groupNames = [];
	private $groupUsers = [];

	function __construct($file) {
		$Spreadsheet = new SpreadsheetReader($file);
		$Spreadsheet->ChangeSheet(0);

		foreach ($Spreadsheet as $k => $v) {
			if ($k == 0) {
				if (!$this->checkHeader($v)) {
					throw new Exception('Excel format is not correct');
				}
				continue;
			}

			if (trim($v[0]) != '') {
				$this->data[] = $v;
			}
		}

		$this->countGroupNames();
		$this->processData();
	}

	public function getGroups() {
		$groups = [];

		foreach ($this->groupUsers as $k1 => $v1) {
			$groups[] = [
				'groupname' => $k1,
				'parentname' => ''
			];

			foreach ($v1 as $k2 => $v2) {
				$groups[] = [
					'groupname' => $k2,
					'parentname' => $k1
				];

				foreach ($v2 as $k3 => $v3) {
					$groups[] = [
						'groupname' => $k3,
						'parentname' => $k2
					];
				}
			}
		}

		return $groups;
	}

	public function getUsers() {
		$users = [];

		foreach ($this->groupUsers as $k1 => $v1) {
			foreach ($v1 as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					foreach ($v3 as $user) {
						$users[] = array_merge(['groupname' => $k3], $user);
					}
				}
			}
		}

		return $users;
	}

	private function checkHeader($header) {
		$headerTemplate = [
			0 => '学号',
			1 => '姓名',
			2 => '民族',
			3 => '性别',
			4 => '学院',
			5 => '专业',
			6 => '班级',
			7 => '',
			8 => '',
			9 => '密码',
		];

		$diff = array_diff_assoc($headerTemplate, $header);

		return empty($diff);
	}

	private function countGroupNames() {
		$groupTree = [];
		foreach ($this->data as $k => $v) {
			$college = $v[4];
			$speciality = $v[5];
			$class = $v[6];
			$groupTree[$college][$speciality][$class] = 1;
		}

		// college
		foreach ($groupTree as $k1 => $v1) {
			if (!isset($this->groupNames[$k1])) {
				$this->groupNames[$k1] = 1;
			} else {
				$this->groupNames[$k1]++;
			}
		}

		foreach ($groupTree as $k1 => $v1) {
			// speciality
			foreach ($v1 as $k2 => $v2) {
				if (!isset($this->groupNames[$k2])) {
					$this->groupNames[$k2] = 1;
				} else {
					$this->groupNames[$k2]++;
				}
			}

			foreach ($v1 as $k2 => $v2) {
				// class
				foreach ($v2 as $k3 => $v3) {
					if (!isset($this->groupNames[$k3])) {
						$this->groupNames[$k3] = 1;
					} else {
						$this->groupNames[$k3]++;
					}
				}
			}
		}
	}

	private function processData() {
		foreach ($this->data as $k => $v) {
			$college = $v[4];
			$speciality = $v[5];
			if ($this->groupNames[$speciality] > 1) {
				$speciality = sprintf("%s（%s）", $speciality, $college);
			}
			$class = $v[6];
			if ($this->groupNames[$class] > 1) {
				$class = sprintf("%s（%s）", $class, $college);
			}

			$this->groupUsers[$college][$speciality][$class][] = [
				'id' => $v[0],
				'name' => $v[1],
				'nation' => $v[2],
				'gender' => $v[3],
				'password' => $v[9]
			];
		}
	}
}
