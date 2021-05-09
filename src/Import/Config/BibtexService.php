<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    BibTeX
 * @package     Opus\Bibtex\Import\Configuration
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Config;

class BibtexService
{
    /**
     * Name der Konfigurationsdatei, in der Feld-Mappings sowie Dokumenttyp-Mappings angegeben werden.
     */
    const INI_FILE = 'import.ini';

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new BibtexService();
        }

        return self::$instance;
    }

    /**
     * Liefert das Feld-Mapping auf Basis der Feld-Mapping-Konfiguration mit dem übergebenen Namen zurück.
     *
     * @param string $mappingConfigName Name der Feld-Mapping-Konfiguration; ist dieser nicht gesetzt, so wird auf
     *                                  das Default-Mapping zurückgegriffen
     * @return BibtexMapping Feld-Mapping von BibTeX-Feldern auf OPUS-Metadatenfelder
     * @throws \Exception
     */
    public function getFieldMapping($mappingConfigName = null)
    {
        if (is_null($mappingConfigName)) {
            $mappingConfigName = 'default';
        }

        $fieldMappings = $this->getConfiguration('fieldMappings');
        foreach ($fieldMappings as $mapping) {
            $mappingFile = $this->getPath($mapping);
            if (file_exists($mappingFile)) {
                // TODO weitere Konfigurationsformate unterstützen?
                if (substr($mapping, -4) === 'json') {
                    $jcr = new JsonBibtexMappingReader();
                    $mappingConf = $jcr->getMappingConfigurationFromFile($mappingFile);
                    if ($mappingConf->getName() === $mappingConfigName) {
                        // es wurde eine Mapping-Konfiguration mit dem übergebenen Namen gefunden
                        return $mappingConf;
                    }
                }
            }
        }

        throw new \Exception('could not find configuration file of field mappings with name ' . $mappingConfigName);
    }

    /**
     * Liefert das Dokumenttyp-Mapping aus, das in der Ini-Datei konfiguriert wurde.
     *
     * @return DocumentTypeMapping Dokumenttyp-Mapping
     * @throws \Exception
     */
    public function getTypeMapping()
    {
        $documentTypeMapping = new DocumentTypeMapping();
        $documentTypeMapping->setDefaultType($this->getConfiguration('defaultDocumentType'));

        $documentTypeMappingConf = $this->getConfiguration('documentTypeMapping');
        foreach ($documentTypeMappingConf as $bibtexTypeName => $opusTypeName) {
            $documentTypeMapping->setMapping($bibtexTypeName, $opusTypeName);
        }
        return $documentTypeMapping;
    }

    /**
     * Gibt die Konfigurationseinstellung aus der INI-Datei zurück, die unter dem übergebenen Schlüsselnamen abgelegt
     * ist.
     *
     * @param string $keyName Name des Konfigurationsschlüssels
     * @return mixed
     * @throws \Exception falls INI-Datei nicht existent, nicht lesbar oder der Schlüsselname nicht existiert.
     */
    private function getConfiguration($keyName)
    {
        $fileName = $this->getPath(self::INI_FILE);

        if (! is_readable($fileName)) {
            throw new \Exception("could not find or read ini file $fileName");
        }

        $conf = parse_ini_file($fileName);
        if ($conf === false) {
            throw new \Exception("could not parse ini file $fileName");
        }

        if (! array_key_exists($keyName, $conf)) {
            throw new \Exception("could not find configuration of fieldMappings in ini file $fileName");
        }

        return $conf[$keyName];
    }

    private function getPath($fileName)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $fileName;
    }
}