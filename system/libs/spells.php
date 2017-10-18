<?php
/**
 * Spells class
 *
 * @package   MyAAC
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.6.1
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');

class Spells {
	private static $spellsList = null;
	private static $lastError = '';
	
	public static function loadFromXML($show = false) {
		global $config, $db;
		
		try { $db->query('DELETE FROM `' . TABLE_PREFIX . 'spells`;'); } catch(PDOException $error) {}
		
		if($show) {
			echo '<h2>Reload spells.</h2>';
			echo '<h2>All records deleted from table <b>' . TABLE_PREFIX . 'spells</b> in database.</h2>';
		}
		
		foreach($config['vocations'] as $voc_id => $voc_name) {
			$vocations_ids[$voc_name] = $voc_id;
		}
		
		try {
			self::$spellsList = new OTS_SpellsList($config['data_path'].'spells/spells.xml');
		}
		catch(Exception $e) {
			self::$lastError = $e->getMessage();
			return false;
		}
		//add conjure spells
		$conjurelist = self::$spellsList->getConjuresList();
		if($show) {
			echo "<h3>Conjure:</h3>";
		}
		
		foreach($conjurelist as $spellname) {
			$spell = self::$spellsList->getConjure($spellname);
			$lvl = $spell->getLevel();
			$mlvl = $spell->getMagicLevel();
			$mana = $spell->getMana();
			$name = $spell->getName();
			$soul = $spell->getSoul();
			$spell_txt = $spell->getWords();
			$vocations = $spell->getVocations();
			$nr_of_vocations = count($vocations);
			$vocations_to_db = "";
			$voc_nr = 0;
			foreach($vocations as $vocation_to_add) {
				if(Validator::number($vocation_to_add)) {
					$vocations_to_db .= $vocation_to_add;
				}
				else
					$vocations_to_db .= $vocations_ids[$vocation_to_add];
				$voc_nr++;
				
				if($voc_nr != $nr_of_vocations) {
					$vocations_to_db .= ',';
				}
			}
			
			$enabled = $spell->isEnabled();
			if($enabled) {
				$hide_spell = 0;
			}
			else {
				$hide_spell = 1;
			}
			$pacc = $spell->isPremium();
			if($pacc) {
				$pacc = '1';
			}
			else {
				$pacc = '0';
			}
			$type = 2;
			$count = $spell->getConjureCount();
			try {
				$db->query('INSERT INTO myaac_spells (spell, name, words, type, mana, level, maglevel, soul, premium, vocations, conjure_count, hidden) VALUES (' . $db->quote($spell_txt) . ', ' . $db->quote($name) . ', ' . $db->quote($spell_txt) . ', ' . $db->quote($type) . ', ' . $db->quote($mana) . ', ' . $db->quote($lvl) . ', ' . $db->quote($mlvl) . ', ' . $db->quote($soul) . ', ' . $db->quote($pacc) . ', ' . $db->quote($vocations_to_db) . ', ' . $db->quote($count) . ', ' . $db->quote($hide_spell) . ')');
				if($show) {
					success("Added: " . $name . "<br>");
				}
			}
			catch(PDOException $error) {
				if($show) {
					warning('Error while adding spell (' . $name . '): ' . $error->getMessage());
				}
			}
		}
		
		//add instant spells
		$instantlist = self::$spellsList->getInstantsList();
		if($show) {
			echo "<h3>Instant:</h3>";
		}
		
		foreach($instantlist as $spellname) {
			$spell = self::$spellsList->getInstant($spellname);
			$lvl = $spell->getLevel();
			$mlvl = $spell->getMagicLevel();
			$mana = $spell->getMana();
			$name = $spell->getName();
			$soul = $spell->getSoul();
			$spell_txt = $spell->getWords();
			if(strpos($spell_txt, '###') !== false)
				continue;
			
			$vocations = $spell->getVocations();
			$nr_of_vocations = count($vocations);
			$vocations_to_db = "";
			$voc_nr = 0;
			foreach($vocations as $vocation_to_add) {
				if(Validator::number($vocation_to_add)) {
					$vocations_to_db .= $vocation_to_add;
				}
				else
					$vocations_to_db .= $vocations_ids[$vocation_to_add];
				$voc_nr++;
				
				if($voc_nr != $nr_of_vocations) {
					$vocations_to_db .= ',';
				}
			}
			$enabled = $spell->isEnabled();
			if($enabled) {
				$hide_spell = 0;
			}
			else {
				$hide_spell = 1;
			}
			$pacc = $spell->isPremium();
			if($pacc) {
				$pacc = '1';
			}
			else {
				$pacc = '0';
			}
			$type = 1;
			$count = 0;
			try {
				$db->query("INSERT INTO myaac_spells (spell, name, words, type, mana, level, maglevel, soul, premium, vocations, conjure_count, hidden) VALUES (".$db->quote($spell_txt).", ".$db->quote($name).", ".$db->quote($spell_txt).", '".$type."', '".$mana."', '".$lvl."', '".$mlvl."', '".$soul."', '".$pacc."', '".$vocations_to_db."', '".$count."', '".$hide_spell."')");
				if($show) {
					success("Added: ".$name."<br/>");
				}
			}
			catch(PDOException $error) {
				if($show) {
					warning('Error while adding spell (' . $name . '): ' . $error->getMessage());
				}
			}
		}
		
		return true;
	}
	
	public static function getSpellsList() {
		return self::$spellsList;
	}
	
	public static function getLastError() {
		return self::$lastError;
	}
}