<?php

class Application_Driver_Storage_DB extends Application_Driver_Storage
{
    public function get($name)
    {
        $entry = DBHelper::fetch(
            "SELECT
                `data_value`,
                `expiry_date`
            FROM
                app_settings
            WHERE
                `data_key`=:data_key",
            array(
                ':data_key' => $name
            )
        );

        $expiryDate = null;
        if (!empty($entry['expiry_date']))
        {
            $expiryDate = DateTime::createFromFormat('Y-m-d H:i:s', $entry['expiry_date']);
        }
        if($expiryDate === null || $expiryDate>new DateTime())
        {
            if (is_array($entry) && isset($entry['data_value']))
            {
                return $entry['data_value'];
            }
        }

        return null;
    }

    public function set($name, $value, $role)
    {
        DBHelper::insertOrUpdate(
            'app_settings',
            array(
                'data_key' => $name,
                'data_value' => $value,
                'data_role' => $role
            ),
            array(
                'data_key'
            )
        );
    }

    public function setExpiry($name, $date)
    {
        DBHelper::update(
            "UPDATE
				`app_settings`
			SET
			    `expiry_date`=:expiry_date
            WHERE
                `data_key`=:data_key",
            array(
                ':expiry_date' => $date,
                ':data_key' => $name
            )
        );
    }

    public function delete($name)
    {
        DBHelper::delete(
            "DELETE FROM
                `app_settings`
            WHERE
                `data_key`=:data_key",
            array(
                ':data_key' => $name
            )
        );
    }
}