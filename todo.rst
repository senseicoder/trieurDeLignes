TODO
####

* Import todoged
* Import manuels
* Import struct rst
* Tri dossier rst, nexus, fichier
* Envoi buffer todoged
* Recherche souple destination 
* Lecture liste rst avec bloc
* Correction rst liste 
* Nouveau groupe
* Annuler dernière action 
* Insérer dans rst
* Trier un dossier dans ses sous dossiers
* Virer les parties des liens utm
* Trieur aussi dans todoged



class CRSTManips
{
	protected $_aLines = array(), $_aStruct = array(), $_aLevels = array();

	function __construct($sPathfile = NULL)
	{
		if($sPathfile !== NULL) $this->Load($sPathfile);
	}

	function Load($sPathfile)
	{
		foreach(file($sPathfile) as $sLine) {
			switch(CRSTLigne::Analyse($sLine)) {

				case CRSTLigne::TITRE : 
					break;

				case CRSTLigne::TEXTE : 
					break;

				case CRSTLigne::PUCE : 
					break;

				case CRSTLigne::SUBTITRE : 
					break;

				case CRSTLigne::UNKNOWN : 
					throw new Exception('ligne non analysée : ' . $sLine); 
					break;

				default: throw new Exception('ligne non traitée : ' . $sLine);
			}
		}
	}

	function Export()
	{
		return $this->_aStruct;
	}

	function ExportLevels()
	{
		return $this->_aLevels;
	}
}
