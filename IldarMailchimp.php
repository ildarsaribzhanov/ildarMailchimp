<?php


/**
 * Класс для работы с АПИ
 *
 * Class IldarMailchimp
 */
class IldarMailchimp
{
	
	/**
	 * АПИ ключь
	 *
	 * @var
	 */
	protected $api_key;
	
	/**
	 * Адрес запроса к апи
	 *
	 * @var string
	 */
	protected $url = 'https://<dc>.api.mailchimp.com/3.0/';
	
	/**
	 * IldarMailchimp constructor.
	 *
	 * @param $key
	 * @param $dc
	 */
	public function __construct($key, $dc)
	{
		$this->api_key = $key;
		$this->url     = str_replace('<dc>', $dc, $this->url);
	}
	
	
	/**
	 * Выполенение запроса к АПИ
	 *
	 * @param string     $method метод запроса
	 * @param array|bool $data   Данные
	 *
	 * @return mixed
	 */
	protected function request($method = '', $type = 'post', $data = false)
	{
		$url = $this->url . $method;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/vnd.api+json',
			'Content-Type: application/vnd.api+json',
			'Authorization: apikey ' . $this->api_key
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		switch ($type) {
			case 'post':
				curl_setopt($ch, CURLOPT_POST, true);
				if (is_array($data)) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				}
				break;
			
			case 'get':
				$query = http_build_query($data, '', '&');
				curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
				break;
			
			case 'delete':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			
			case 'patch':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
				if (is_array($data)) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				}
				break;
			
			case 'put':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				if (is_array($data)) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				}
				break;
		}
		
		$out = curl_exec($ch);
		curl_close($ch);
		
		return json_decode($out);
	}
	
	
	/**
	 * Получить список доступных листов рассылки
	 *
	 * @return array|mixed|object
	 */
	public function getLists()
	{
		return $this->request('lists', 'get');
	}
	
	/**
	 * Добаление подписчика
	 *
	 * @param string $list
	 * @param string $email
	 * @param string $name
	 */
	public function addSubscriber($list = '', $email = '', $name = '')
	{
		$data = array(
			'email_address' => $email,
			'status'        => 'pending',
			'merge_fields'  => array('FNAME' => $name, 'LNAME' => '')
		);
		
		$res = $this->request('lists/' . $list . '/members', 'post', $data);
		
		$return = 'На указанную почту придет письмо с подтвержением подписки.';
		
		if ($res->status == 400) {
			switch ($res->title) {
				case 'Member Exists':
					$return = 'Вы уже подписались ранее';
					break;
				
				default:
					$return = $res->title;
					break;
			}
		}
		
		return $return;
	}
	
	/**
	 * Списко компаний
	 *
	 * @return mixed
	 */
	public function getCampaigns()
	{
		$res = $this->request('campaigns', 'get');
		
		return $res;
	}
	
	
	/**
	 * Создание компании
	 *
	 * @param string $list_id
	 * @param string $subj
	 * @param string $from_name
	 * @param string $reply_to
	 *
	 * @return mixed
	 */
	public function createCamping($list_id = '', $subj, $from_name, $reply_to)
	{
		$data = array(
			'type'       => 'regular',
			'recipients' => array('list_id' => $list_id),
			'settings'   => array(
				'subject_line' => $subj,
				'reply_to'     => $reply_to,
				'from_name'    => $from_name
			)
		);
		
		$res = $this->request('campaigns', 'post', $data);
		
		return $res;
	}
	
	/**
	 * Создание текста для компании
	 *
	 * @param $html
	 * @param $id_camping
	 *
	 * @return mixed
	 */
	public function createCampingContent($plain, $html, $id_camping)
	{
		$data = array('plain_text' => $plain, 'html' => $html);
		
		$res = $this->request('campaigns/' . $id_camping . '/content', 'put', $data);
		
		return $res;
	}
	
	
	/**
	 * Отправка тестового письма для компании
	 *
	 * @param string $id_camping компании
	 * @param string $email      куда отправлять тестовое сообщение
	 *
	 * @return mixed
	 */
	public function testCamping($id_camping, $email = '')
	{
		$data = array('test_emails' => array($email), 'send_type' => 'html');
		
		$res = $this->request('campaigns/' . $id_camping . '/actions/test', 'post', $data);
		
		return $res;
	}
	
	
	/**
	 * Отправка компании
	 *
	 * @param $id_camping id компани
	 */
	public function sendCamping($id_camping = '')
	{
		$res = $this->request('campaigns/' . $id_camping . '/actions/send', 'post');
		
		return $res;
	}
	
	/**
	 * Получить список доступных шаблонов
	 *
	 * @return mixed
	 */
	public function getTemplates()
	{
		$res = $this->request('templates/', 'get');
		
		return $res;
	}
}