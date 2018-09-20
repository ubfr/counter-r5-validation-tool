

CREATE TABLE `provider_details` (
  `id` bigint(255) UNSIGNED NOT NULL,
  `configuration_id` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `provider_name` varchar(255) DEFAULT NULL,
  `provider_url` varchar(255) DEFAULT NULL,
  `apikey` varchar(255) DEFAULT NULL,
  `requestor_id` varchar(255) DEFAULT NULL,
  `customer_id` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `remarks` varchar(255) NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `provider_details`
--

INSERT INTO `provider_details` (`id`, `configuration_id`, `provider_name`, `provider_url`, `apikey`, `requestor_id`, `customer_id`, `status`, `remarks`, `time_stamp`) VALUES
(1, '28', 'sdfdsf', 'asdf', 'asdf', 'asdf', 'asdf', 1, '', '2018-09-06 11:39:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `provider_details`
--
ALTER TABLE `provider_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `provider_details`
--
ALTER TABLE `provider_details`
  MODIFY `id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

