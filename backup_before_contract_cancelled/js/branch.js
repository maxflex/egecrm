	
	function getColorById(id_branch)
	{
		switch (id_branch) {
				// Оранжевый
				case self::TRG: case self::KLG: {
					color = "#FBAA33";
					break;
				}
				// Красный
				case self::PVN: {
					color = "#EF1E25";
					break;
				}
				// Голубой
				case self::BGT: {
					color = "#019EE0";
					break;
				}
				// Синий
				case self::STR:
				case self::IZM:
				case self::MLD: {
					color = "#0252A2";
					break;
				}
				// Фиолетовый
				case self::OPL:
				case self::RPT: {
					color = "#B61D8E";
					break;
				}
				// Зеленый
				case self::VKS:
				case self::ORH: {
					color = "#029A55";
					break;
				}
				// Серый
				case self::PRR:
				case self::VLD:
				case self::PRG: {
					color = "#ACADAF";
					break;
				}
				// Желтый
				case self::NVG: {
					color = "#FFD803";
					break;
				}
				// Салатовый
				case self::BRT: {
					color = "#B1D332";
					break;
				}
			}
	}