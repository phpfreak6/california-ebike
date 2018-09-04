<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Addresses.
 *
 * Contains methods for getting and validating origin and destination addresses.
 *
 * @author 	Simple Sales Tax
 * @package SST
 * @since 	5.0
 */
class SST_Addresses {

	/**
	 * Converts an Address to a formatted string.
	 *
	 * @since 5.0
	 *
	 * @param  Address $address
	 * @return string
	 */
	public static function format( $address ) {
		return sprintf( '%s, %s, %s %s', $address->getAddress1(), $address->getCity(), $address->getState(), $address->getZip5() );
	}

	/**
	 * Determines whether an address is "valid." An address is considered to be
	 * valid if a city, state, address, and ZIP code are provided.
	 *
	 * @since 5.0
	 *
	 * @param  Address $address
	 * @return bool
	 */
	public static function is_valid( $address ) {
		if ( is_null( $address ) )
			return false;

		$required = array( $address->getCity(), $address->getState(), $address->getZip5() );

		foreach ( $required as $value ) {
			if ( empty( $value ) ) 
				return false;
		}
			
		return true;
	}

	/**
	 * Return business address with given location key.
	 *
	 * @since 5.0
	 *
	 * @param  int $index Location key.
	 * @return SST_Origin_Address|NULL
	 */
	public static function get_address( $index ) {
		$addresses = self::get_origin_addresses();
		
		if ( isset( $addresses[ $index ] ) )
			return $addresses[ $index ];

		return NULL;
	}

	/**
	 * Verify an address.
	 *
	 * @since 5.0
	 *
	 * @param  Address $address
	 * @return Address
	 */
	public static function verify_address( $address ) {
		$addresses = get_transient( 'sst_verified_addresses' );
		
		if ( ! is_array( $addresses ) ) {
			$addresses = array();
		}

		$md5_hash = md5( json_encode( $address ) );

		if ( array_key_exists( $md5_hash, $addresses ) ) {
			$decoded = json_decode( $addresses[ $md5_hash ], true );
			
			$address = new TaxCloud\Address(
				$decoded['Address1'],
				$decoded['Address2'],
				$decoded['City'],
				$decoded['State'],
				$decoded['Zip5'],
				$decoded['Zip4']
			);
		} else {
			$request = new TaxCloud\Request\VerifyAddress( SST_Settings::get( 'tc_id' ), SST_Settings::get( 'tc_key' ), $address );
			try {
				$address = TaxCloud()->VerifyAddress( $request );
			} catch ( Exception $ex ) {
				// Leave address as-is
			}

			$addresses[ $md5_hash ] = json_encode( $address );

			// Cache validated addresses for 3 days
			set_transient( 'sst_verified_addresses', $addresses, 2 * DAY_IN_SECONDS );
		}

		return $address;
	}

	/**
	 * Get all default origin addresses.
	 *
	 * @since 5.0
	 *
	 * @return SST_Origin_Address[]
	 */
	public static function get_default_addresses() {
		$return    = array();
		$addresses = self::get_origin_addresses();
		
		foreach ( $addresses as $address ) {
			if ( $address->getDefault() ) {
				$return[ $address->getID() ] = $address; 
			}
		}
		
		return $return;
	}
	/**
	 * Get default pickup address.
	 *
	 * @since 5.0
	 *
	 * @return SST_Origin_Address|NULL
	 */
	public static function get_default_address() {
		$defaults = self::get_default_addresses();

		if ( ! empty( $defaults ) ) {
			return current( $defaults );
		}
		
		return NULL;
	}

	/**
	 * Get all business addresses configured by the admin.
	 *
	 * @since 5.0
	 *
	 * @return SST_Origin_Address[] Array of SST_Origin_Address.
	 */
	public static function get_origin_addresses() {
		$raw_addresses = SST_Settings::get( 'addresses' );

		if ( ! is_array( $raw_addresses ) ) {
			return array();
		}

		$addresses = array();

		foreach ( $raw_addresses as $raw_address ) {
			$address = json_decode( $raw_address, true );
			
			$addresses[] = new SST_Origin_Address(
				$address['ID'],
				$address['Default'],
				$address['Address1'],
				$address['Address2'],
				$address['City'],
				$address['State'],
				$address['Zip5'],
				$address['Zip4']
			);
		}

		return $addresses;
	}

	/**
	 * Convert an SST_Origin_Address object to an Address object.
	 *
	 * @since 5.0
	 *
	 * @param  SST_Origin_Address $address
	 * @return Address
	 */
	public static function to_address( $address ) {
		if ( is_null( $address ) || ! is_a( $address, 'SST_Origin_Address' ) ) {
			return null;
		} else {
			return new TaxCloud\Address(
				$address->getAddress1(),
				$address->getAddress2(),
				$address->getCity(),
				$address->getState(),
				$address->getZip5(),
				$address->getZip4()
			);
		}
	}
}