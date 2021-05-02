<?php

namespace App\Utils;

use App\Document\File;
use App\Utils\Catalog\PictureHelpers;
use Aws\S3\S3Client;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class FileManager
{
    const MODE_S3 = 'S3';
    const MODE_LOCAL = 'LOCAL';

    /**
     * @var string
     */
    private string $uploadDir;

    /**
     * Local or AWS
     * @var string
     */
    private string $mode;

    /**
     * @var S3Client
     */
    private S3Client $s3Client;

    /**
     * @var string
     */
    private string $bucket;

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @var IdGenerator
     */
    private IdGenerator $idGenerator;

    public function __construct(
        KernelInterface $kernel,
        RequestStack $requestStack,
        IdGenerator $idGenerator
    )
    {
        $this->idGenerator = $idGenerator;
        $this->mode = isset($_ENV['FILE_SOURCE']) && (strtoupper($_ENV['FILE_SOURCE']) === self::MODE_S3) ? self::MODE_S3 : self::MODE_LOCAL;
        if ($this->mode === self::MODE_S3) {
            $this->s3Client = new S3Client([
                'version' => 'latest',
                'region' => $_ENV['S3_REGION'],
                'endpoint' => $_ENV['S3_ENDPOINT'],
                'credentials' => [
                    'key' => $_ENV['S3_ACCESS_KEY'],
                    'secret' => $_ENV['S3_SECRET_KEY'],
                ]
            ]);
            $this->bucket = $_ENV['S3_BUCKET'];
            $this->baseUrl = $_ENV['S3_ENDPOINT'];

            $url = parse_url($_ENV['S3_ENDPOINT']);
            $this->baseUrl = $url['scheme'] . '://' . $_ENV['S3_BUCKET'] . '.' . $url['host'];
        }
        if ($this->mode === self::MODE_LOCAL) {
            $this->uploadDir = $kernel->getProjectDir() . '/public/uploads';
            $this->baseUrl = BaseUrl::fromRequestStack($requestStack) . '/uploads';
        }
    }

    /**
     * Take an uploaded file and upload it on the file system, then return an id to get the same file later
     * @param UploadedFile $uploadedFile
     * @return File
     * @throws Exception
     */
    public function parse(UploadedFile $uploadedFile): File
    {
        $components = explode('.', $uploadedFile->getClientOriginalName());
        $extension = end($components);
        return (new File())
            ->setName($this->idGenerator->generateStr(12) . '.' . $extension)
            ->setMimeType($uploadedFile->getMimeType())
            ->setSize($uploadedFile->getSize())
            ->setOriginalFileName($uploadedFile->getClientOriginalName())
            ->setHash(PictureHelpers::hashFile($uploadedFile));
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param File $file
     * @return void
     */
    public function upload(UploadedFile $uploadedFile, File $file): void
    {
        if ($this->mode === self::MODE_S3) {
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $file->getName(),
                'Body' => fopen($uploadedFile->getRealPath(), 'r'),
                'ACL' => 'public-read'
            ]);
            return;
        }
        $uploadedFile->move($this->uploadDir, $file->getName());
    }

    /**
     * @param File $file
     */
    public function remove(File $file): void
    {
        if ($this->mode === self::MODE_S3) {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $file->getName()
            ]);
            return;
        }
        $path = $this->uploadDir . DIRECTORY_SEPARATOR . $file->getName();
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function generateUrl(File $file): string
    {
        return $this->baseUrl . '/' . $file->getName();
    }
}
