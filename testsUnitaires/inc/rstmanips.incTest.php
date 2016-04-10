<?php

require_once CHEMIN_CODE . '/inc/rstlayers.class.php';

class testCRstLayers extends PHPUnit_Framework_TestCase
{
	private $_aDocument;

	function assertLigneTitre($i, $iLevel, $iParent, array $aChildren = array())
	{
		$this->AssertEquals(CRSTLigne::TITRE, $this->_aDocument['lines'][$i]['nature']);		
		$this->AssertEquals($iLevel, $this->_aDocument['lines'][$i]['titrelevel']);
		$this->AssertEquals($iParent, $this->_aDocument['lines'][$i]['parent']);
		$this->AssertEquals($aChildren, $this->_aDocument['lines'][$i]['children']);
	}

	function assertLignePuce($i, $iLevel, $iParent, array $aChildren = array())
	{
		$this->AssertEquals(CRSTLigne::PUCE, $this->_aDocument['lines'][$i]['nature']);		
		$this->AssertEquals($iLevel, $this->_aDocument['lines'][$i]['pucelevel']);
		$this->AssertEquals($iParent, $this->_aDocument['lines'][$i]['parent']);
		$this->AssertEquals($aChildren, $this->_aDocument['lines'][$i]['children']);
	}

	function testChargement_Titres()
	{
		$a = CRstLayers::Charger(__DIR__ . '/rst/titre.rst');
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
		$a = CRstLayers::Charger(__DIR__ . '/rst/titres_et_puces.rst');
		$this->_aDocument = $a;

		$i = 0;
		$this->assertLigneTitre($i++, 0, NULL, array(3));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 1, 0, array(9));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);

		$this->assertLignePuce($i++, 0, 0);
		$this->assertLignePuce($i++, 0, 0);
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);

		$this->assertLigneTitre($i++, 2, 3, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);	

		$this->assertLignePuce($i++, 0, 3);
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);
		$this->assertLignePuce($i++, 1, 12);
		$this->assertLignePuce($i++, 1, 12);
	}
}