<?php
	// Контроллер
	class MangoController extends Controller
	{
		// Папка вьюх
		protected $_viewsFolder	= "mango";
		
		/**
		 * Поступил входящий звонок
		 */
		public function actionEventCall()
		{
			$data = json_decode($_POST['json']);
			
			// исходящий звонок
			if ($data->from->extension) {
				switch ($data->call_state) {
					case Mango::STATE_APPEARED:
						User::setCallBusy($data->from->extension);
						break;
					case Mango::STATE_DISCONNECTED:
						User::setCallFree($data->from->extension);
						break;
				}
			}
			
			// входящий звонок в ЕГЭ-Центр или ЕГЭ-Репетитор
			if (in_array($data->to->line_number, [Call::EGEREP_NUMBER, Call::EGECENTR_NUMBER])) {
				// @рассмотреть добавление определения в appeared
				switch ($data->call_state) {
					case Mango::STATE_APPEARED:
						// определить последнего говорившего
						$data->caller			= Call::getCaller($data->from->number);
						$data->last_call_data 	= Call::lastData($data->from->number);
						break;
					case Mango::STATE_CONNECTED:
						User::setCallBusy($data->to->extension);
						Call::notifyAnswered($data->to->extension, $data->call_id);
						break;
					case Mango::STATE_DISCONNECTED:
						User::setCallFree($data->to->extension);
						break;
				}
				// уведомляем о входящем звонке
				Call::notifyIncoming($data->to->extension, $data, $data->to->line_number);
			}
		}
		
		/*
		 * Положили трубку (не используется)
		 */
		public function actionHangup()
		{
			extract($_POST);
			Mango::hangup($call_id);
		}
	}
