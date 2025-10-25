<?php

namespace App\services;

class ServicesCSV
{
    private string $projectDir;

    public function __construct(string $projectDir) {
        $this->projectDir = $projectDir;
    }

    public function readCSV(string $filename): array {
        $path = $this->projectDir.'/assets/csv/'.$filename;
        
        if (!file_exists($path))
            throw new \RuntimeException("Файл '$filename' не существует или не найден");

        $data = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false){
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        return $data;
    } 

    public function writeCSV(string $filename, array $data): void {
        $path = $this->projectDir.'/assets/csv/'.$filename;

        if (!file_exists($path)) 
            throw new \RuntimeException("Файл '$filename' не существует или не найден");
        
        if (empty($data))
            throw new \InvalidArgumentException("Данные не могут быть пустыми");

        $expectedHeaders = $this->getCsvHeaders($path);
        
        # Предполагается, что новая запись передается просто как строка (1, aboba, 42, 54), т.е. не ассоциативный массив
        # При желании можно добавить функцию для нормальной валидации, так как запись по идее возможна только в csv
        # с бронированием, ну или запрашивать конкретно ассоциативный массив, где мы сможем однозначно знать, подходят ли нам новые данные
        if (count($data) !== count($expectedHeaders)) {
            throw new \InvalidArgumentException(
                sprintf("Ожидалось %d полей, получено %d", count($expectedHeaders), count($data))
            );
        }

        if (($handle = fopen($path, 'a')) !== false) {
            fputcsv($handle, $data);
            fclose($handle);
        }
    }

    private function getCsvHeaders(string $path): array {

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            fclose($handle);

            if ($headers === false || empty($headers)) {
                throw new \RuntimeException("Файл не содержит заголовков");
            }

            return $headers;
        }

        throw new \RuntimeException("Не удалось прочитать файл при получении заголовков");
    }

    public function updateCsv(string $filename, string $id, array $newData): void {
        $path = $this->projectDir.'/assets/csv/'.$filename;

        # Проверка на существование произойдет при чтении
        $data = $this->readCSV($filename);
        $updated = false;

        foreach($data as &$row) {
            if ($row['id'] === $id) {
                $row = array_merge($row, $newData);
                $updated = true;
                break;
            }
        }

        if (!$updated) 
            throw new \RuntimeException("Запись с id - '$id' не была найдена");

        if (($handle = fopen($path, 'w')) !== false) {
            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        }
        unset($row);
    }
}
