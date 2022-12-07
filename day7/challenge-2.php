<?php

$go = new Go();
$go->run();

class Go
{
    private const TOTAL_FILESYSTEM_SIZE = 70_000_000;
    private const SPACE_NEEDED_FOR_UPDATE = 30_000_000;

    private int $needToDeleteSize = 0;

    private ?int $minSizeOfDirWeCanDeleteForUpdate = null;

    public function run(): void
    {
        $crawler = new DirCrawler();
        $crawler->readAndParseCommands();

        $totalUsedSpace = $crawler->getTotalUsedSpace();
        print "Total used space is: $totalUsedSpace\n";

        $spaceLeftOnDevice = self::TOTAL_FILESYSTEM_SIZE - $totalUsedSpace;
        print "Space left on device is: $spaceLeftOnDevice\n";

        $this->needToDeleteSize = self::SPACE_NEEDED_FOR_UPDATE - $spaceLeftOnDevice;
        print "We need to delete: $this->needToDeleteSize\n";

        $crawler->treeNodeVisitor(null, function(array $node) {
            $totalSize = $node['total_size'];

            if ($totalSize < $this->needToDeleteSize) {
                return;
            }

            if ($this->minSizeOfDirWeCanDeleteForUpdate === null) {
                $this->minSizeOfDirWeCanDeleteForUpdate = $totalSize;
            } else if ($totalSize < $this->minSizeOfDirWeCanDeleteForUpdate) {
                $this->minSizeOfDirWeCanDeleteForUpdate = $totalSize;
            }
        });
    }
}

class DirCrawler
{
    private const COMMAND_PREFIX = '$';
    private const COMMAND_CD = 'cd';
    private const COMMAND_LS = 'ls';

    private string $currentPath = '';
    private string $currentCommand = '';

    private array $tree = [
        '/' => [
            'children' => [],
            'total_size' => 0,
        ],
    ];

    private array $currentNode = [];

    /**
     * @throws Exception
     */
    function readAndParseCommands(): void
    {
        $fp = fopen('input.txt', 'r');
        if ($fp === false) {
            throw new Exception('Unable to load input data');
        }

        while ($line = fgets($fp)) {
            $line = trim($line);
            if (empty($line)) {
                break;
            }

            if ($line[0] === self::COMMAND_PREFIX) {
                $commandElements = explode(' ', $line);

                $command = $this->getCommand($commandElements[1]);
                $commandInfo = $this->getCommandInfo($command, $commandElements);
                $this->handleCommand($command, $commandInfo);
            } else {
                $this->handleLine($line);
            }
        }

        fclose($fp);
    }

    private function handleCommand(string $command, string $commandInfo): void
    {
        switch ($command)
        {
            case self::COMMAND_CD:
                $this->currentCommand = '';
                $this->handleCd($commandInfo);
                break;

            case self::COMMAND_LS:
                $this->currentCommand = self::COMMAND_LS;
                break;

            default:
                throw new Exception("Unhandled command $command");
        }
    }

    private function handleLine(string $line): void
    {
        if (empty($line)) {
            return;
        }

        if ($this->currentCommand === self::COMMAND_LS) {
            $elements = explode(' ', $line);

            if (count($elements) !== 2) {
                throw new Exception("Invalid line in LS: $line");
            }

            if (is_numeric($elements[0])) {
                $numBytes = (int)$elements[0];
                $this->addBytesForCurrentPath($numBytes);
            }
        }
    }

    public function treeNodeVisitor(?array $currentNode = null, callable $handleNodeCallback): void
    {
        if ($currentNode === null) {
            $currentNode = $this->tree['/'];
        }

        $handleNodeCallback($currentNode);

        foreach ($currentNode['children'] as $childNode) {
            $this->treeNodeVisitor($childNode, $handleNodeCallback);
        }
    }

    public function getTotalUsedSpace(): int
    {
        return (int)$this->tree['/']['total_size'];
    }

    private function addBytesForCurrentPath(int $numBytes): void
    {
        if ($this->currentPath === '/') {
            $node = &$this->tree['/'];
            $node['total_size'] += $numBytes;
            return;
        }

        $currentPath = '/';
        $node = &$this->tree[$currentPath];
        $node['total_size'] += $numBytes;

        $directories = explode('/', $this->currentPath);

        foreach ($directories as $dir) {
            $dir = trim($dir);
            if (empty($dir)) {
                continue;
            }

            if ($currentPath !== '/') {
                $currentPath .= '/';
            }

            $currentPath .= $dir;

            if (isset($node['children'][$currentPath])) {
                $node = &$node['children'][$currentPath];
                $node['total_size'] += $numBytes;
            } else {
                throw new Exception("Node for current path: {$currentPath} should already exist when adding bytes");
            }
        }
    }

    private function setCurrentNodeForCurrentPath(bool $shouldAlreadyExist): void
    {
        if ($this->currentPath === '/') {
            $this->currentNode = &$this->tree['/'];
            return;
        }

        $currentPath = '/';
        $currentNode = &$this->tree[$currentPath];

        // We need to traverse the tree to set the current note
        $directories = explode('/', $this->currentPath);

        foreach ($directories as $dir) {
            $dir = trim($dir);
            if (empty($dir)) {
                continue;
            }

            if ($currentPath !== '/') {
                $currentPath .= '/';
            }

            $currentPath .= $dir;

            if (isset($currentNode['children'][$currentPath])) {
                $currentNode = &$currentNode['children'][$currentPath];
            } else {
                if ($shouldAlreadyExist) {
                    throw new Exception("Node for current path: {$this->currentPath} should already exist");
                }

                $currentNode['children'][$currentPath] = [
                    'children' => [],
                    'total_size' => 0,
                ];

                $currentNode = &$this->currentNode['children'][$this->currentPath];
            }
        }

        $this->currentNode = &$currentNode;
    }

    private function handleCd(string $path): void
    {
        if ($path === '/') {
            $this->currentPath = '/';
            $this->currentNode = &$this->tree['/'];
            return;
        }

        if ($path === '..') {
            $lastSlashPos = strrpos($this->currentPath, '/');
            if ($lastSlashPos > 0) {
                // Trim off the last directory in the path.
                $this->currentPath = substr($this->currentPath, 0, $lastSlashPos);
            }

            $this->setCurrentNodeForCurrentPath(true);

            return;
        }

        // Append a slash to the current path, as long as the current path is not the root path.
        if ($this->currentPath !== '/') {
            $this->currentPath = $this->currentPath . '/';
        }

        // Add the new directory name to the path.
        $this->currentPath .= $path;

        $this->setCurrentNodeForCurrentPath(false);
    }

    /**
     * @throws Exception
     */
    private function getCommand(string $command): string
    {
        return match ($command) {
            self::COMMAND_CD, self::COMMAND_LS => $command,
            default => throw new Exception("Invalid command $command"),
        };
    }

    /**
     * @throws Exception
     */
    private function getCommandInfo(string $command, array $commandElements): string
    {
        if ($command === self::COMMAND_LS) {
            return '';
        }

        if (count($commandElements) < 3) {
            throw new Exception("Invalid command $command");
        }

        $info = $commandElements[2];

        if (empty($info)) {
            throw new Exception("Invalid command info for $command");
        }

        return $info;
    }
}


