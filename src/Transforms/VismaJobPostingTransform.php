<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms;

use SchemaTransformer\Interfaces\AbstractDataTransform;
use SchemaTransformer\IO\HttpXmlReader;
use Spatie\SchemaOrg\Schema;
use SimpleXMLElement;


class VismaJobPostingTransform implements AbstractDataTransform
{
    /**
     * @param \SchemaTransformer\Interfaces\SanitizerInterface[] $sanitizers
     */

    private $guidGroup;
    public function __construct(private array $sanitizers, string $guidGroup)
    {
        $this->guidGroup = $guidGroup;
        error_log($this->guidGroup);
    }

    private function formatText($text) {

        $text = str_replace(["\r\n\r\n", "\n\n", "\r\r"], '[[paragraph]]', $text);
    

        $text = nl2br($text);
    
        $paragraphs = explode('[[paragraph]]', $text);
    
      
        $formattedText = '';
        foreach ($paragraphs as $para) {
            $formattedText .= '<p>' . trim($para) . '</p>';
        }
    
        return $formattedText;
    }

    private function getSingleItemData(string $guid)
    {
        $reader = new HttpXmlReader();
        $data = $reader->read('https://recruit.visma.com/External/Feeds/AssignmentItem.ashx?guidGroup=' . $this->guidGroup . '&guidAssignment=' . $guid);

        $xmlString = $data['content'];
        $cleanXml = $this->sanitizeXML($xmlString);
        $xml = new SimpleXMLElement($cleanXml, LIBXML_NOCDATA | LIBXML_NOWARNING);
        $assignment_item = $xml->xpath('//Assignment');

        return $assignment_item ?? [];
    }

    private function getContactPersons($assignment): array
    {
        $contacts = [];
        $contactNodes = $assignment->Localization->AssignmentLoc->ContactPersons->ContactPerson ?? [];

        foreach ($contactNodes as $contact) {
            $contacts[] = Schema::contactPoint()
                ->contactType((string)$contact->Title)
                ->name((string)$contact->ContactName)
                ->email((string)$contact->Email)
                ->telephone((string)$contact->Telephone);
        }
        return $contacts;
    }


    public function transform(array $data): array
    {
        $output = [];

        if (empty($data['content'])) {
            throw new \InvalidArgumentException("No XML content provided");
        }

        $xmlString = $data['content'];

        try {
            $cleanXml = $this->sanitizeXML($xmlString);

            if (str_contains($cleanXml, 'Kunde inte hitta gruppen')) {
                   die("Process terminated due to invalid group"); 
            }
    
            $xml = new SimpleXMLElement($cleanXml, LIBXML_NOCDATA | LIBXML_NOWARNING);
            $assignments = $xml->xpath('//Assignment');
        } catch (\Exception $e) {
            error_log("XML Parse Error: " . $e->getMessage());
            return [];
        }
 
        

        foreach ($assignments as $assignment) {
            try {
                $localization = $assignment->Localization->AssignmentLoc[0] ?? null;

                if (!$localization) {
                    continue;
                }
                $guid = (string)$assignment->Guid ?? null;

                if (!$guid) {
                    continue;
                }
                
                $departments = $localization->xpath('Departments/Department');

                $accountName = (string)$assignment->AccountName;
                $ownerDept = $localization->xpath('Departments/Department[@Type="Owner"]/Name');
                $ownerName = !empty($ownerDept) ? (string)$ownerDept[0] : '';


                $organizations = [
                    ['nameorgunit' => $accountName ?: ''],
                    ['nameorgunit' => $ownerName ?: '']
                ];

                [$org, $unit] = $this->normalizeArray($organizations, 2, ["nameorgunit" => ""]);

                $county = [
                    'name' => (string)($localization->County->Name ?? '')
                ];
                $municipality = [
                    'name' => (string)($localization->Municipality->Name ?? '')
                ];

                $assignment_item = $this->getSingleItemData($guid);
                $single_item = $assignment_item[0];

                $direct_apply = (string) $single_item->ApplicationMethods->ApplicationMethod->ValueXml->web->url;
                $fullDescription = "<h2>Om arbetsplatsen</h2>";
                $fullDescription .= (string)$single_item->Localization->AssignmentLoc->DepartmentDescr ?? '';
                $fullDescription .= "<h2>Arbetsuppgifter</h2>";
                $fullDescription .=  (string)$single_item->Localization->AssignmentLoc->WorkDescr ?? '';
                $fullDescription .= "<h2>Kvalifikationer</h2>";
                $fullDescription .= (string)$single_item->Localization->AssignmentLoc->Qualifications ?? '';
                $fullDescription .= "<h2>Övrig information</h2>";
                $fullDescription .= (string)$single_item->Localization->AssignmentLoc->AdditionalInfo ?? '';
                $fullDescription = $this->formatText($fullDescription);

                $jobPosting = Schema::jobPosting()
                    ->identifier((string)$assignment->RefNo)
                    ->totalJobOpenings((string)$assignment->NumberOfJobs)
                    ->title((string)$localization->AssignmentTitle)
                    ->description($fullDescription)
                    ->jobStartDate((string)$localization->EmploymentStartDateDescr)
                    ->responsibilities((string)$localization->WorkDescr)
                    ->datePosted($this->formatDate((string)$assignment->PublishStartDate))
                    ->experienceRequirements((string)$localization->WorkExperiencePrerequisite->Name)
                    ->employmentType((string)$localization->EmploymentGrade->Name ?? '')
                    ->workHours((string)$localization->EmploymentType->Name ?? '')
                    ->validThrough($this->formatDate((string)$assignment->ApplicationEndDate))
                    ->url($direct_apply)
                    ->directApply($direct_apply);

                if (!empty($org['nameorgunit'])) {
                    $jobPosting->hiringOrganization(
                        Schema::organization()->name($org['nameorgunit'])
                    );
                }

                if (!empty($contacts = $this->getContactPersons($single_item))) {
                    $jobPosting->applicationContact($contacts);
                }

                if (!empty($unit['nameorgunit'])) {
                    $organization = Schema::organization()->name($unit['nameorgunit']);

                    if (!empty($county['name']) || !empty($municipality['name'])) {
                        $address = Schema::postalAddress();
                        if (!empty($county['name'])) {
                            $address->addressRegion($county['name']);
                        }
                        if (!empty($municipality['name'])) {
                            $address->addressLocality($municipality['name']);
                        }
                        if (!empty($localization->Country->Name)) {
                            $address->addressCountry((string)$localization->Country->Name);
                        }
                        $organization->address($address);
                    }

                    $jobPosting->employmentUnit($organization);
                }


                $jobPosting->setProperty('@version', md5(json_encode($jobPosting->toArray())));
                $output[] = $jobPosting->toArray();
            } catch (\Exception $e) {
                error_log($e->getMessage());
                continue;
            }
        }

        error_log("Total job postings processed: " . count($output));
        return $output;
    }

    private function formatDate(string $date)
    {
        return $formattedDate = date('Y-m-d', strtotime($date));
    }

    protected function normalizeArray(?array $in, int $length, array $fallback): array
    {
        if (empty($in) || !is_array($in)) {
            $in = [];
        }
        return array_pad($in, $length, $fallback);
    }

    private function sanitizeXML(string $xml): string
    {
        // Replace unescaped ampersands in URLs
        $xml = preg_replace('/&(?!(?:amp|quot|apos|lt|gt);)/', '&amp;', $xml);

        // Remove any invalid XML characters
        $xml = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $xml);

        return $xml;
    }
}
