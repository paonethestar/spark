<?php

namespace ProcessMaker\Managers;

use ProcessMaker\Exception\CalendarInformationException;
use ProcessMaker\Model\CalendarAssignment;
use ProcessMaker\Model\CalendarBusinessHours;
use ProcessMaker\Model\CalendarDefinition;
use ProcessMaker\Model\CalendarHolidays;
use ProcessMaker\Model\Process;
use ProcessMaker\Model\Task;
use ProcessMaker\Model\User;
use Ramsey\Uuid\Uuid;
use stdClass;

class CalendarManager
{
    const CALENDAR_UID_DEFAULT = '00000000000000000000000000000001';

    /**
     * Load calendar of default
     *
     * @return CalendarDefinition
     */
    public function getCalendarDefault()
    {
        $calendarDefault = new CalendarDefinition(['CALENDAR_UID' => self::CALENDAR_UID_DEFAULT]);
        $definition = $this->getCalendarDefinition($calendarDefault, false);
        if (empty($definition) || empty($definition->toArray())) {
            $this->saveCalendarInformation([
                'CALENDAR_UID' => '00000000000000000000000000000001',
                'CALENDAR_NAME' => __('ID_DEFAULT_CALENDAR'),
                'CALENDAR_CREATE_DATE' => date('Y-m-d'),
                'CALENDAR_UPDATE_DATE' => date('Y-m-d'),
                'CALENDAR_DESCRIPTION' => __('ID_DEFAULT_CALENDAR'),
                'CALENDAR_STATUS' => 'ACTIVE',
                'CALENDAR_WORK_DAYS' => '1|2|3|4|5',
                'BUSINESS_DAY' => [
                    [
                        'CALENDAR_BUSINESS_DAY' => 7,
                        'CALENDAR_BUSINESS_START' => '09:00',
                        'CALENDAR_BUSINESS_END' => '17:00',
                    ]
                ],
                'HOLIDAY' => []
            ]);
            $definition = $this->getCalendarDefault();
        }
        return $definition;
    }

    /**
     * Get Calendar definition
     *
     * @param CalendarDefinition $calendar
     * @param bool $default If the calendar does not exist, return to the default calendar.
     *
     * @return CalendarDefinition
     */
    public function getCalendarDefinition(CalendarDefinition $calendar, $default = false)
    {
        $definition = CalendarDefinition::where('CALENDAR_UID', $calendar->CALENDAR_UID)->first();
        if ($default && (empty($definition) || empty($definition->toArray()))) {
            $definition = $this->getCalendarDefault();
        }
        return $definition;
    }

    /**
     * Get Calendar information (Definition, holidays, business hours)
     *
     * @param CalendarDefinition $calendar
     * @param bool $validate
     *
     * @return CalendarDefinition
     */
    public function getCalendarInformation(CalendarDefinition $calendar, $validate = false)
    {
        $definition = $this->getCalendarDefinition($calendar, true);
        $definition->BUSINESS_DAY = $definition->businessHours()->get()->toArray();
        $definition->HOLIDAY = $definition->holidays()->get()->toArray();
        if ($validate) {
            $definition = $this->validateCalendarInformation($definition->toArray());
        }
        return $definition;
    }

    /**
     * Validate information of calendar
     *
     * @param array $data
     * @return stdClass
     */
    public function validateCalendarInformation($data)
    {
        try {
            $calendar = new CalendarDefinition();

            //Validate if Working days are Correct, by default minimum 3
            $workingDays = explode('|', $data['CALENDAR_WORK_DAYS']);
            if (count($workingDays) < 3) {
                throw new CalendarInformationException('You must define at least 3 Working Days!');
            }
            //Validate that all Working Days have Business Hours
            if (count($data['BUSINESS_DAY']) < 1) {
                throw new CalendarInformationException('You must define at least one Business Day for all days');
            }
            $workingDaysOK = [];
            foreach ($workingDays as $day) {
                $workingDaysOK[$day] = false;
            }
            $sw_all = false;
            foreach ($data['BUSINESS_DAY'] as $businessHours) {
                if ($businessHours['CALENDAR_BUSINESS_DAY'] == 7) {
                    $sw_all = true;
                } elseif (in_array($businessHours['CALENDAR_BUSINESS_DAY'], $workingDays)) {
                    $workingDaysOK[$businessHours['CALENDAR_BUSINESS_DAY']] = true;
                }
            }
            $sw_days = true;

            foreach ($workingDaysOK as $sw_day) {
                $sw_days = $sw_days && $sw_day;
            }
            if (!($sw_all || $sw_days)) {
                throw new CalendarInformationException('Not all working days have their correspondent business day');
            }

            $calendar->fill($data);
            $calendar->BUSINESS_DAY = $data['BUSINESS_DAY'];
            $calendar->HOLIDAY = $data['HOLIDAY'];
            return $calendar;
        } catch (CalendarInformationException $e) {
            $calendar->CALENDAR_UID = self::CALENDAR_UID_DEFAULT;
            return $this->getCalendarInformation($calendar);
        }
    }

    /**
     * Get calendar assignment by user, process or task
     *
     * @param User $user
     * @param Process $process
     * @param Task $task
     * @param bool $validate If information validation is required
     *
     * @return stdClass
     */
    public function getCalendarAssignment(User $user, Process $process, Task $task, $validate = true)
    {
        $assignment = CalendarAssignment::whereIn('OBJECT_UID', [$user->USR_UID, $process->PRO_UID, $task->TAS_UID])->get()->toArray();
        $calendarDefinition = new CalendarDefinition();
        $calendarDefinition->CALENDAR_UID = self::CALENDAR_UID_DEFAULT;
        $owner = 'DEFAULT';
        if ($assignment) {
            $last = end($assignment);
            switch ($last['OBJECT_UID']) {
                case $user->USR_UID:
                    $calendarDefinition->CALENDAR_UID = $last['CALENDAR_UID'];
                    $owner = 'USER';
                    break;
                case $process->PRO_UID:
                    $calendarDefinition->CALENDAR_UID = $last['CALENDAR_UID'];
                    $owner = 'PROCESS';
                    break;
                case $task->TAS_UID:
                    $calendarDefinition->CALENDAR_UID = $last['CALENDAR_UID'];
                    $owner = 'TASK';
                    break;
            }
        }
        $definition = $this->getCalendarInformation($calendarDefinition, $validate);
        $definition->OWNER = $owner;
        return $definition;
    }

    /**
     * Save all Information of calendar
     *
     * @param $information
     *
     * @return CalendarDefinition
     * @throws \Throwable
     */
    public function saveCalendarInformation($information)
    {
        $definition = $this->saveCalendarDefinition($information);
        if (isset($information['BUSINESS_DAY']) && is_array($information['BUSINESS_DAY'])) {
            foreach ($information['BUSINESS_DAY'] as $businessHours) {
                $businessHours['CALENDAR_UID'] = $definition->CALENDAR_UID;
                $businessHours['CALENDAR_ID'] = $definition->CALENDAR_ID;
                $this->saveCalendarBusinessHours($businessHours);
            }
        }
        if (isset($information['HOLIDAY']) && is_array($information['HOLIDAY'])) {
            foreach ($information['HOLIDAY'] as $holiday) {
                $holiday['CALENDAR_UID'] = $definition->CALENDAR_UID;
                $holiday['CALENDAR_ID'] = $definition->CALENDAR_ID;
                $this->saveCalendarHolidays($holiday);
            }
        }
        return $this->getCalendarInformation($definition);
    }

    /**
     * Save Calendar Definition
     *
     * @param array $data
     *
     * @return CalendarDefinition
     * @throws \Throwable
     */
    public function saveCalendarDefinition($data): CalendarDefinition
    {
        $definition = new CalendarDefinition();
        if (!isset($data['CALENDAR_UID']) || empty($data['CALENDAR_UID'])) {
            $data['CALENDAR_UID'] = str_replace('-', '', Uuid::uuid4());
        }
        $definition->fill($data);
        $definition->saveOrFail();
        return $definition;
    }

    /**
     * Save Calendar Business Hours
     *
     * @param array $data
     *
     * @return CalendarBusinessHours
     * @throws \Throwable
     */
    public function saveCalendarBusinessHours($data)
    {
        $businessHours = new CalendarBusinessHours();
        $businessHours->fill($data);
        $businessHours->saveOrFail();
        return $businessHours;

    }

    /**
     * Save Calendar Business Hours
     *
     * @param array $data
     *
     * @return CalendarHolidays
     * @throws \Throwable
     */
    public function saveCalendarHolidays($data)
    {
        $holidays = new CalendarHolidays();
        $holidays->fill($data);
        $holidays->saveOrFail();
        return $holidays;
    }

    /**
     * Save calendar Assignment
     *
     * @param array $data
     *
     * @return CalendarAssignment
     * @throws \Throwable
     */
    public function saveCalendarAssignment($data)
    {
        $assignment = new CalendarAssignment();
        $assignment->fill($data);
        $assignment->saveOrFail();
        return $assignment;
    }
}