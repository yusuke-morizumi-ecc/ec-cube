<?php


namespace Eccube\Entity\Api;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessToken
 *
 * @ORM\Table(name="dtb_access_token")
 * @ORM\Entity(repositoryClass="Eccube\Repository\Api\AccessTokenRepository")
 */
class AccessToken
{
    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=200)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $access_token;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz", nullable=false)
     */
    private $create_date;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expire_date", type="datetimetz", nullable=false)
     */
    private $expire_date;

    /**
     * @param string $token
     * @return AccessToken
     */
    public function setAccessToken(string $token): AccessToken
    {
        $this->access_token = $token;
        return $this;
    }


    /**
     * @param \DateTime $createDate
     * @return AccessToken
     */
    public function setCreateDate(\DateTime $createDate): AccessToken
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * @param \DateTime $expireDate
     * @return AccessToken
     */
    public function setExpireDate(\DateTime $expireDate): AccessToken
    {
        $this->expire_date = $expireDate;

        return $this;
    }
}
