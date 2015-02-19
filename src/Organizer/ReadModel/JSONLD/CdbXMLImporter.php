<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Organizer\ReadModel\JSONLD;

/**
 * Takes care of importing actors in the CdbXML format (UDB2) that represent
 * an organizer, into a UDB3 JSON-LD document.
 */
class CdbXMLImporter
{
    /**
     * Imports a UDB2 organizer actor into a UDB3 JSON-LD document.
     *
     * @param \stdClass $base
     *   The JSON-LD document to start from.
     * @param \CultureFeed_Cdb_Item_Actor $actor
     *   The actor data from UDB2 to import.
     *
     * @return \stdClass
     *   The document with the UDB2 actor data merged in.
     */
    public function documentWithCdbXML(
        $base,
        \CultureFeed_Cdb_Item_Actor $actor
    ) {
        $jsonLD = clone $base;

        $detail = null;

        /** @var \CultureFeed_Cdb_Data_Detail[] $details */
        $details = $actor->getDetails();

        foreach ($details as $languageDetail) {
            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
                $detail = $languageDetail;
            }
        }

        $jsonLD->name = $detail->getTitle();

        $jsonLD->addresses = array();
        $contact_cdb = $actor->getContactInfo();
        if ($contact_cdb) {
            /** @var \CultureFeed_Cdb_Data_Address[] $addresses * */
            $addresses = $contact_cdb->getAddresses();

            foreach ($addresses as $address) {
                $address = $address->getPhysicalAddress();

                if ($address) {
                    $jsonLD->addresses[] = array(
                        'addressCountry' => $address->getCountry(),
                        'addressLocality' => $address->getCity(),
                        'postalCode' => $address->getZip(),
                        'streetAddress' =>
                            $address->getStreet() . ' ' .
                            $address->getHouseNumber(),
                    );
                }
            }

            $emails_cdb = $contact_cdb->getMails();
            if (count($emails_cdb) > 0) {
                $emails = array();
                foreach ($emails_cdb as $mail) {
                    $emails[] = $mail->getMailAddress();
                }
                $jsonLD->email = $emails;
            }

            $phones_cdb = $contact_cdb->getPhones();
            if (count($phones_cdb) > 0) {
                $phones = array();
                foreach ($phones_cdb as $phone) {
                    $phones[] = $phone->getNumber();
                }
                $jsonLD->phone = $phones;
            }

        }

        return $jsonLD;
    }
}
