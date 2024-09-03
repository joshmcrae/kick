<?php

namespace Kick\Http;

class Router
{
    /**
     * @param Route[]
     */
    private array $routes = [];

    /**
     * Router constructor.
     *
     * @param string $path 
     * @return void
     */
    public function __construct(public string $path)
    {
    }

    /**
     * Returns the route matching a request, or null if no route matches.
     *
     * @param Request $request
     * @param array $segments
     * @return Route|null
     */
    public function match(Request $request, array &$segments = []): ?Route
    {
        foreach ($this->getRoutes() as $route) {
            if ($route->matches($request, $segments)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns all routes defined under the registered filesystem path.
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        if (!$this->path) {
            return [];
        }

        if (!empty($this->routes)) {
            return $this->routes;
        }

        $files = $this->findRouteFiles($this->path);

        foreach ($files as $file) {
            $this->routes[] = $this->filepathToRoute(...$file);
        }

        return $this->routes;
    }
    
    /**
     * Recursively searches a path for route files.
     *
     * A route file is any file with  the .php extension, except when the file 
     * is named __middleware.php, in which case it is expected to declare 
     * middleware for all files under the path where it was encountered.
     *
     * @param string $path
     * @param string[] $middleware
     * @param string $prefix
     * @return array{string,string[]}
     */
    private function findRouteFiles(
        string $path, 
        array $middleware = [], 
        string $prefix = ''
    ): array {
        $iter = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
        $dirs = [];
        $files = [];

        // Collect middleware files
        foreach ($iter as $entry) {
            if ($entry->getFilename() === '__middleware.php') {
                $middleware[] = $prefix . str_replace(
                    realpath($path), '', 
                    realpath($entry)
                );
            }
        }

        // Collect route files and directories
        foreach ($iter as $entry) {
            if ($entry->isDir()) {
                $dirs[] = (string) $entry;

                continue;
            }


            if ($entry->getFilename() === '__middleware.php') {
                continue;
            }

            $files[] = [
                $prefix . str_replace(realpath($path), '', realpath($entry)), 
                $middleware
            ];
        }

        foreach ($dirs as $dir) {
            $files = array_merge(
                $files,
                $this->findRouteFiles(
                    $dir,
                    $middleware,
                    $prefix . '/' . pathinfo($dir, PATHINFO_BASENAME)
                )
            );
        }

        return $files;
    }

    /**
     * Returns a route based on a filepath.
     *
     * @param string   $filepath
     * @param string[] $middleware
     * @return Route
     */
    private function filepathToRoute(string $filepath, array $middleware): Route
    {
        $path = [rtrim(pathinfo($filepath, PATHINFO_DIRNAME), '/')];

        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $parts = explode('.', $filename);

        $terminal = array_shift($parts);
        $method = strtoupper(array_shift($parts) ?? 'ANY');

        if ($terminal !== 'index') {
            $path[] = $terminal;
        }

        $uri = preg_replace('/_(\w+)/', ':$1', implode('/', $path));

        if (empty($uri)) {
            $uri = '/';
        }

        return new Route(
            $method, 
            $uri,
            $filepath,
            $middleware
        );
    }
}
