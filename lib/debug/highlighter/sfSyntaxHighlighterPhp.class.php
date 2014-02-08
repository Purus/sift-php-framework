<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Php code highlighter
 *
 * @package Sift
 * @subpackage debug_highlighter
 */
class sfSyntaxHighlighterPhp extends sfSyntaxHighlighterGeneric
{
  /**
   * Setups the regexes
   *
   */
  protected function setup()
  {
    $this->addStringPattern();
    $this->addMathPattern();
    // Numbers (also look for Hex)
    $this->addPattern('/(?<!\w)(0x[\da-f]+|\d+)(?!\w)/ix', '<span class="' . $this->getCssPrefix() . 'number">$1</span>');
    $this->addVarPattern();

    $this->addPattern('/(?<!\w|\$|\%|\@|>)(
                and|or|xor|for|do|while|foreach|as|return|die|exit|if|then|else|
                elseif|new|delete|try|throw|catch|finally|class|function|string|
                array|object|resource|var|bool|boolean|int|integer|float|double|
                real|string|array|global|const|static|public|private|protected|
                published|extends|switch|true|false|null|void|this|self|struct|
                char|signed|unsigned|short|long
            )(?!\w|=")/ix', '<span class="' . $this->getCssPrefix() . 'keyword">$1</span>');

    $this->addPattern('/(class|namespace)\s([^\s|\;]+)/', '$1 <span class="' . $this->getCssPrefix() . 'method">$2</span>');
    $this->addPattern('/(include(_once)?|namespace|require(_once)?)([(\s|\()])/', '<span class="' . $this->getCssPrefix() . 'keyword">$1</span>$4');
    $this->addPattern('/new\s([^()\$]+)/', '<span class="' . $this->getCssPrefix() . 'keyword">new</span> <span class="' . $this->getCssPrefix() . 'function">$1</span>');

    $this->addPattern('/extends\s([^\s]+)/', 'extends <span class="' . $this->getCssPrefix() . 'method"><em>$1</em></span>');
    $this->addPattern('/([\:]{2})([A-Z_0-9]+)(;|,|:|\))/', '::<span class="' . $this->getCssPrefix() . 'constant">$2</span>$3');
    $this->addPattern('/([\\\a-zA-Z_0-9]+)([\:]{2})/', '<span class="' . $this->getCssPrefix() . 'class">$1</span><span class="' . $this->getCssPrefix() . 'keyword">::</span>');
    $this->addPattern('/\.(?![^\'\"\s]*([\'\"]))/', '<span class="' . $this->getCssPrefix() . 'keyword">.</span>');
    $this->addPattern('/default/', '<span class="' . $this->getCssPrefix() . 'keyword">default</span>');
    $this->addPattern('/function\s(.+)\(/', 'function <span class="' . $this->getCssPrefix() . 'method">$1</span>(');
    $this->addPattern('/([^_])array\(/', '$1<span class="' . $this->getCssPrefix() . 'function">array</span>(');
    $this->addPattern('/(\()([a-zA-Z0-9]+)(\s)(\$)/', '$1<span class="' . $this->getCssPrefix() . 'function">$2</span> $');

    $this->addPattern('/instanceof\s(.+)\)/', '<span class="' . $this->getCssPrefix() . 'keyword">instanceof</span> <span class="' . $this->getCssPrefix() . 'function">$1</span>)');

    // namespace stuff
    $this->addPattern('/use\s(.*);/', '<span class="' . $this->getCssPrefix() . 'keyword">use</span> <span class="' . $this->getCssPrefix() . 'function">$1</span>;');
    $this->addPattern('/\\\/', '<span class="' . $this->getCssPrefix() . 'default">\\</span>');
    $this->addPattern('/,/', '<span class="' . $this->getCssPrefix() . 'default">,</span>');

    // keep this in??
    $this->addPattern('/echo/', '<span class="' . $this->getCssPrefix() . 'function">echo</span>');

    $this->addPattern('/(?i)\b(s(huffle|ort)|is_null|header|var_dump|date|setcookie|n(ext|at(sort|casesort))|c(o(unt|mpact)|urrent)|in_array|(strto|micro)time|u(sort|ksort|asort)|prev|e(nd|xtract)|k(sort|ey|rsort)|a(sort|r(sort|ray_(s(hift|um|plice|earch|lice)|c(h(unk|ange_key_case)|o(unt_values|mbine))|intersect(_(u(key|assoc)|key|assoc))?|diff(_(u(key|assoc)|key|assoc))?|u(n(shift|ique)|intersect(_(uassoc|assoc))?|diff(_(uassoc|assoc))?)|p(op|ush|ad|roduct)|values|key(s|_exists)|f(il(ter|l(_keys)?)|lip)|walk(_recursive)?|r(e(duce|verse)|and)|m(ultisort|erge(_recursive)?|ap))))|r(sort|eset|ange)|m(in|ax)|highlight_(string|file)|s(ys_getloadavg|et_(include_path|magic_quotes_runtime)|leep)|c(on(stant|nection_(status|aborted))|all_user_(func(_array)?|method(_array)?))|time_(sleep_until|nanosleep)|i(s_uploaded_file|n(i_(set|restore|get(_all)?)|et_(ntop|pton))|p2long|gnore_user_abort|mport_request_variables)|u(sleep|nregister_tick_function)|error_(log|get_last)|p(hp_strip_whitespace|utenv|arse_ini_file|rint_r)|flush|long2ip|re(store_include_path|gister_(shutdown_function|tick_function))|get(servby(name|port)|opt|_(c(urrent_user|fg_var)|include_path|magic_quotes_(gpc|runtime))|protobyn(umber|ame)|env)|move_uploaded_file|s(tr(nc(asecmp|mp)|c(asecmp|mp)|len)|et_e(rror_handler|xception_handler))|c(lass_exists|reate_function)|trigger_error|i(s_(subclass_of|a)|nterface_exists)|de(fine(d)?|bug_(print_backtrace|backtrace))|zend_version|property_exists|e(ach|rror_reporting|xtension_loaded)|func(tion_exists|_(num_args|get_arg(s)?))|leak|restore_e(rror_handler|xception_handler)|get_(class(_(vars|methods))?|included_files|de(clared_(classes|interfaces)|fined_(constants|vars|functions))|object_vars|extension_funcs|parent_class|loaded_extensions|resource_type)|method_exists|sys_get_temp_dir|copy|t(empnam|mpfile)|u(nlink|mask)|p(close|open)|f(s(canf|tat|eek)|nmatch|close|t(ell|runcate)|ile(_(put_contents|get_contents))?|open|p(utcsv|assthru)|eof|flush|write|lock|read|get(s(s)?|c(sv)?))|r(e(name|a(dfile|lpath)|wind)|mdir)|get_meta_tags|mkdir|stat|c(h(own|grp|mod)|learstatcache)|is_(dir|executable|file|link|writable|readable)|touch|disk_(total_space|free_space)|file(size|ctime|type|inode|owner|_exists|perms|atime|group|mtime)|l(stat|chgrp)|srand|getrandmax|rand|mt_(srand|getrandmax|rand)|hebrev(c)?|s(scanf|imilar_text|tr(s(tr|pn)|natc(asecmp|mp)|c(hr|spn|oll)|i(str|p(slashes|cslashes|os|_tags))|t(o(upper|k|lower)|r)|_(s(huffle|plit)|ireplace|pad|word_count|r(ot13|ep(eat|lace)))|p(os|brk)|r(chr|ipos|ev|pos))|ubstr(_(co(unt|mpare)|replace))?|etlocale)|c(h(unk_split|r)|ount_chars)|nl(2br|_langinfo)|implode|trim|ord|dirname|uc(first|words)|join|pa(thinfo|rse_str)|explode|quotemeta|add(slashes|cslashes)|wordwrap|l(trim|ocaleconv)|rtrim|money_format|b(in2hex|asename))(?=\s*\()/', '<span class="' . $this->getCssPrefix() . 'function">$1</span>');
    $this->addPattern('/(image(s(y|tring(up)?|et(style|t(hickness|ile)|pixel|brush)|avealpha|x)|c(har(up)?|o(nvolution|py(res(ized|ampled)|merge(gray)?)?|lor(s(total|et|forindex)|closest(hwb|alpha)?|transparent|deallocate|exact(alpha)?|a(t|llocate(alpha)?)|resolve(alpha)?|match))|reate(truecolor|from(string|jpeg|png|wbmp|g(if|d(2(part)?)?)|x(pm|bm)))?)|2wbmp|t(ypes|tf(text|bbox)|ruecolortopalette)|i(struecolor|nterlace)|d(estroy|ashedline)|jpeg|ellipse|p(s(slantfont|copyfont|text|e(ncodefont|xtendfont)|freefont|loadfont|bbox)|ng|olygon|alettecopy)|f(t(text|bbox)|il(ter|l(toborder|ed(polygon|ellipse|arc|rectangle))?)|ont(height|width))|wbmp|a(ntialias|lphablending|rc)|l(ine|oadfont|ayereffect)|r(otate|ectangle)|g(if|d(2)?|ammacorrect|rab(screen|window))|xbm)|jpeg2wbmp|png2wbmp|gd_info)(?=\s*\()/', '<span class="' . $this->getCssPrefix() . 'function">$1</span>');
    $this->addPattern('/(?i)\bmysqli_(s(sl_set|t(ore_result|at|mt_(s(tore_result|end_long_data|qlstate)|num_rows|close|in(sert_id|it)|data_seek|p(aram_count|repare)|e(rr(no|or)|xecute)|f(ield_count|etch|ree_result)|a(ttr_(set|get)|ffected_rows)|res(ult_metadata|et)|bind_(param|result)))|e(t_local_infile_(handler|default)|lect_db)|qlstate)|n(um_(fields|rows)|ext_result)|c(ha(nge_user|racter_set_name)|ommit|lose)|thread_(safe|id)|in(sert_id|it|fo)|options|d(ump_debug_info|ebug|ata_seek)|use_result|p(ing|repare)|err(no|or)|kill|f(ield_(seek|count|tell)|etch_(field(s|_direct)?|lengths|row)|ree_result)|warning_count|a(utocommit|ffected_rows)|r(ollback|eal_(connect|escape_string|query))|get_(server_(info|version)|host_info|client_(info|version)|proto_info)|more_results)(?=\s*\()/', '<span class="' . $this->getCssPrefix() . 'function">$1</span>');
  }

}
