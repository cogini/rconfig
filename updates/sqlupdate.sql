-- Dumping structure for table DATABASE_NAME.compliancePolElem
CREATE TABLE IF NOT EXISTS `compliancePolElem` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `elementName` varchar(255) NOT NULL,
  `elementDesc` varchar(255) NOT NULL,
  `singleParam1` int(10) DEFAULT NULL COMMENT '1, equals. 2, contains',
  `singleLine1` varchar(255) DEFAULT NULL,
  `status` int(10) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

-- Dumping structure for table DATABASE_NAME.compliancePolElemTbl
CREATE TABLE IF NOT EXISTS `compliancePolElemTbl` (
  `polId` int(10) DEFAULT NULL,
  `elemId` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- Dumping structure for table DATABASE_NAME.compliancePolicies
CREATE TABLE IF NOT EXISTS `compliancePolicies` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `policyName` varchar(255) DEFAULT NULL,
  `policyDesc` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;


-- Dumping structure for table DATABASE_NAME.complianceReportPolTbl
CREATE TABLE IF NOT EXISTS `complianceReportPolTbl` (
  `reportId` int(10) DEFAULT NULL,
  `polId` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED;


-- Dumping structure for table DATABASE_NAME.complianceReports
CREATE TABLE IF NOT EXISTS `complianceReports` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `reportsName` varchar(255) DEFAULT NULL,
  `reportsDesc` varchar(255) DEFAULT NULL,
  `status` int(10) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

-- Dumping structure for table DATABASE_NAME.tasks
ALTER TABLE tasks ADD `complianceId` int(10) DEFAULT NULL AFTER mailErrorsOnly;