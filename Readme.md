[![pipeline status](https://git.rinsvent.ru/symfony/bundles/request-bundle/badges/master/pipeline.svg)](https://git.rinsvent.ru/symfony/bundles/request-bundle/-/commits/master)
[![coverage report](https://git.rinsvent.ru/symfony/bundles/request-bundle/badges/master/coverage.svg)](https://git.rinsvent.ru/symfony/bundles/request-bundle/-/commits/master)

Request bundle
=== 

Bundle умеет конвертировать request в DTO
Полученная DTO валидируется. 
В случае ошибок выполнение прекращается и возвращаются ошибки.
В случае успеха DTO присваиваются в атрибуты и доступны в методе контроллера.

### Пример запроса
```json
{
  "signin": {
    "transport": "phone",
    "value": "8888888888",
    "code": "password"  
  }
}
```
### Пример DTO 
```php
namespace App\DTO\Request;

use Rinsvent\Data2DTO\Attribute\HandleTags;
use Rinsvent\Data2DTO\Attribute\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as ProjectAssert;

use Rinsvent\Data2DTO\Attribute\PropertyPath;
use Rinsvent\Data2DTOBundle\Service\Transformer\Request\Headers\Header;
use Rinsvent\Data2DTOBundle\Service\Transformer\Request\Headers\UserAgent;
use Rinsvent\Data2DTOBundle\Service\Transformer\Request\Server\Ip;

#[HandleTags(method: 'getTags')]
class SigninRequest
{    
    #[Assert\NotBlank(message: "error.transport.empty")]
    public string $transport;

    #[Assert\NotBlank(message: "error.value.empty")]
    #[Assert\Email(message: "error.email.wrong", groups: "email")]
    #[ProjectAssert\Phone(message: "error.phone.wrong", groups: "phone")]
    public string $value;

    #[Assert\NotBlank(message: "error.code.empty")]
    public string $code;
    #[VirtualProperty]
    public Device $device;

    public function getTags(array $data, array $tags)
    {
        $transport = $data['transport'] ?? null;
        if ($transport) {
            $tags[] = $transport;
        }
        return $tags;
    }
}

class Device
{
    #[Assert\NotBlank(message: "error.device.id.empty")]
    #[Header(property: 'X-Device-Id')]
    public string $deviceId;

    #[Assert\NotBlank(message: "error.device.source.empty")]
    #[Header(property: 'X-Source')]
    public string $source;

    #[Assert\NotBlank(message: "error.device.ip.empty")]
    #[Ip]
    public ?string $ip = null;

    #[Assert\NotBlank(message: "error.device.user_agent.empty")]
    #[UserAgent]
    public string $userAgent;
}
 ```
### Использование
```php
namespace App\Controller;

use App\Form\Type\User\DTO\SigninRequest;
use App\Service\Entity\UserService;
use Rinsvent\RequestBundle\Annotation\RequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private UserService $us
    ) {}

    #[Route('/v1/signin', name: 'signin', methods: ['POST'])]
    #[RequestDTO(className: SigninRequest::class, jsonPath: '$.signin')]
    public function signin(SigninRequest $signinRequest)
    {
        $signinResponse = $this->us->signin($signinRequest);

        return new JsonResponse(
            [],
            Response::HTTP_OK,
            [
                'X-Access-Token' => $signinResponse->getAccessToken(),
                'X-Refresh-Token' => $signinResponse->getRefreshToken(),
            ]
        );
    }
    
    // Вариант с несколькими DTO
    #[Route('/v1/signin', name: 'signin', methods: ['POST'])]
    #[RequestDTO(className: SigninRequest::class, jsonPath: '$.signin')]
    #[RequestDTO(className: Device::class)]
    public function signin2(SigninRequest $signinRequest, Device $device)
    {
        $signinResponse = $this->us->signin2($signinRequest, $device);
        return new JsonResponse();
    }
}
```
