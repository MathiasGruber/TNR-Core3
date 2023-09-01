<div class="page-box">
    <div class="page-title">
        Jutsu Details
    </div>

    <div class="page-content">

        <div class="page-grid page-column-2">

            <div class="text-left">
                Name: {$data['name']}
            </div>

            <div class="text-left">
                Required Weapons: {$data['required_weapons']}
            </div>

            <div class="text-left">
                Attack type: {$data['attack_type']|capitalize}
            </div>

            <div class="text-left">
                Required Reagents: {$data['required_reagents']}
            </div>

            <div class="text-left">
                Type: {$data['jutsu_type']|capitalize}
            </div>

            <div class="text-left">
                Required Rank: {$data['required_rank']}
            </div>

            <div class="text-left">
                Element: {$data['element']|capitalize}
            </div>

            <div class="text-left">
                Uses / Battle: {$data['max_uses']|capitalize}
            </div>

            <div class="text-left">
                Experience: {$data['exp']}
            </div>

            <div class="text-left">
                Village: {$data['village']}
            </div>

            <div class="text-left">
                Chakra cost: {$data['cha_cost']}
            </div>

            <div class="text-left">
                Level: {$data['level']}
            </div>

            <div class="text-left">
                Stamina cost: {$data['sta_cost']}
            </div>

            <div class="text-left">
                Ryo cost to train: {$data['price'] + ($data['price_increment'] * $data['level'])}
            </div>

            <div class="text-left">
                Targeting Type: {$data['targeting_type']}
            </div>

        </div>

        {if isset($data['specialNote'])}
            <div class="page-sub-title">
                Special Note
            </div>

            <div>
                {$data['specialNote']}
            </div>
        {/if}

        <div class="page-sub-title">
            Description
        </div>

        <div>
            {$data['description']}
        </div>

        <div class="page-sub-title">
            Jutsu Effects
        </div>

        <ul class="text-left">
            {if strlen($effects) > 15}
                {str_replace('-new-line-', "\r\n", $effects)}
            {else}
                N/A
            {/if}
        </ul>
    </div>
</div>