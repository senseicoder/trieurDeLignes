<?php

require_once CHEMIN_CODE . '/inc/rstlayers.class.php';

class testCRstLayers extends PHPUnit_Framework_TestCase
{
	private $_aDocument;

	function assertLigneTitre($i, $iLevel, $iParent, array $aChildren = array())
	{
		$this->AssertEquals(CRSTLigne::TITRE, $this->_aDocument['lines'][$i]['nature']);		
		$this->AssertEquals($iLevel, $this->_aDocument['lines'][$i]['level']);
		$this->AssertEquals($iParent, $this->_aDocument['lines'][$i]['parent']);
		$this->AssertEquals($aChildren, $this->_aDocument['lines'][$i]['children']);
	}

	function assertLignePuce($i, $iLevel, $iParent, array $aChildren = array())
	{
		$this->AssertEquals(CRSTLigne::PUCE, $this->_aDocument['lines'][$i]['nature']);		
		$this->AssertEquals($iLevel, $this->_aDocument['lines'][$i]['level']);
		$this->AssertEquals($iParent, $this->_aDocument['lines'][$i]['parent']);
		$this->AssertEquals($aChildren, $this->_aDocument['lines'][$i]['children']);
	}

	function testChargement_Titres()
	{
		$o = CRstLayers::Charger(__DIR__ . '/rst/titre.rst');
		$a = $o->Get();
		$this->_aDocument = $a;

		$this->AssertEquals('#', $a['levels'][0]);
		$this->AssertEquals('=', $a['levels'][1]);
		$this->AssertEquals('-', $a['levels'][2]);

		$this->AssertEquals(array(0), $a['level0']);

		$i = 0;
		$this->assertLigneTitre($i++, 0, NULL, array(3, 12));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 1, 0, array(6, 9));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 3, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 3, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 1, 0, array(15, 18, 21));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 12, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 12, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 12, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);

		$this->AssertEquals(count($a['lines']), $i);
	}

	function testChargement_Puces()
	{
		$o = CRstLayers::Charger(__DIR__ . '/rst/titres_et_puces.rst');
		$a = $o->Get();
		$this->_aDocument = $a;

		$idSujet1 = 3;
		$idSujet2 = 9;

		$i = 0;
		$this->assertLigneTitre($i++, 0, NULL, array(3));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 1, 0, array(6, 7, 9));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);

		$this->assertLignePuce($i++, 2, $idSujet1);
		$this->assertLignePuce($i++, 2, $idSujet1);
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);

		$this->assertLigneTitre($i++, 2, $idSujet1, array(12));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);	

		$this->assertLignePuce($i++, 3, $idSujet2, array(14, 15));
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);
		$this->assertLignePuce($i++, 4, 12);
		$this->assertLignePuce($i++, 4, 12);
	}

	function testChargement_Textes()
	{
		$o = CRstLayers::Charger(__DIR__ . '/rst/titres_et_textes.rst');
		$a = $o->Get();
		$this->_aDocument = $a;

		$idSujet1 = 3;
		$idSujet2 = 9;

		$i = 0;
		$this->assertLigneTitre($i++, 0, NULL, array(3));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 1, 0, array(6, 7, 9));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);

		$this->AssertEquals(CRSTLigne::TEXTE, $a['lines'][$i++]['nature']);
		$this->AssertEquals(CRSTLigne::TEXTE, $a['lines'][$i++]['nature']);
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);

		$this->assertLigneTitre($i++, 2, $idSujet1, array(12));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);	

		$this->AssertEquals(CRSTLigne::TEXTE, $a['lines'][$i++]['nature']);
	}

	function testEcritureRst()
	{
		$sTmp = tempnam(sys_get_temp_dir(), 'tst');
		$sFile = __DIR__ . '/rst/titres_et_puces.rst';

		$o = CRstLayers::Charger($sFile);
		$o->Ecrire($sTmp);

		$this->assertFileEquals($sFile, $sTmp);
	}

	function testSupressionLignePuce()
	{
		$sTmp = tempnam(sys_get_temp_dir(), 'tst');
		$sFile = __DIR__ . '/rst/titres_et_puces.rst';
		$sFileAttendu = __DIR__ . '/rst/titres_et_puces_suppressionpuce.rst';

		$o = CRstLayers::Charger($sFile);
		$o->Suppression(6);
		$o->Ecrire($sTmp);

		$this->assertFileEquals($sFileAttendu, $sTmp);
	}

	function testSupressionDeuxLignesPuces()
	{
		$sTmp = tempnam(sys_get_temp_dir(), 'tst');
		$sFile = __DIR__ . '/rst/titres_et_puces.rst';
		$sFileAttendu = __DIR__ . '/rst/titres_et_puces_suppression2puces.rst';

		$o = CRstLayers::Charger($sFile);
		$o->Suppression(6);
		$o->Suppression(7);
		$o->Ecrire($sTmp);

		$this->assertFileEquals($sFileAttendu, $sTmp);
	}

	function testAjoutLignePuce()
	{
		$sTmp = tempnam(sys_get_temp_dir(), 'tst');
		$sFile = __DIR__ . '/rst/titres_et_puces.rst';
		$sFileAttendu = __DIR__ . '/rst/titres_et_puces_ajoutpuce.rst';

		$o = CRstLayers::Charger($sFile);
		$o->Ajout(3, CRSTLigne::PUCE, 'puce1c');
		$o->Ecrire($sTmp);

		$a = $o->Get();

		$this->assertEquals(CRSTLigne::PUCE, $a['lines'][8]['nature']);
		$this->assertEquals('puce1c', $a['lines'][8]['value']);
		$this->assertEquals('* puce1c', $a['lines'][8]['raw']);
		$this->assertEquals(3, $a['lines'][8]['parent']);
		$this->assertEquals($a['lines'][7]['level'], $a['lines'][8]['level']);
		$this->assertEquals($a['lines'][7]['level'], $a['lines'][8]['pucelevel']);
		$this->assertEquals(array(), $a['lines'][8]['children']);
		
		$this->assertFileEquals($sFileAttendu, $sTmp);
	}
}