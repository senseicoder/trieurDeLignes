<?php

require_once CHEMIN_CODE . '/inc/rstoperations.class.php';

class testCACategoriserLignes extends PHPUnit_Framework_TestCase
{
	function testAnalyseSubTitre()
	{
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('####'));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('==='));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('------'));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('~~~~~~~~~~~'));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('------  '));

		$this->AssertNotEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser(' ------'));
		$this->AssertNotEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('--'));
	}

	function testAnalysePuce()
	{
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('* puce'));
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('  * puce'));
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('    * puce'));
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser(' * puce'));

		$this->AssertNotEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('*puce'));
	}

	function testVide()
	{
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser(''));	
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser("\n"));	
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser('	'));	
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser('   '));	
	}

	function testParagraphe()
	{
		$this->AssertEquals(CRSTLigne::TEXTE, CACategoriserLignes::Analyser('a'));	
		$this->AssertEquals(CRSTLigne::TEXTE, CACategoriserLignes::Analyser("aaaa"));	
		$this->AssertEquals(CRSTLigne::TEXTE, CACategoriserLignes::Analyser('ceci est une ligne'));	
		$this->AssertEquals(CRSTLigne::TEXTE, CACategoriserLignes::Analyser('encore une ligne et, voilà'));		
	}
}