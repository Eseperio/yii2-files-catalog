<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use eseperio\admintheme\helpers\Html;
use yii\base\Widget;

class Tree extends Widget
{
    public $nodes = [];

    public function run()
    {
        $out = Html::beginTag('ul');
        $nodeDepth = $currDepth = $counter = 0;

        foreach ($this->nodes as $node) {
            $nodeOptions = ['class' => ''];
            if ($nodeDepth == $currDepth) {
                if ($counter > 0) {
                    $out .= "</li>\n";
                }
            } elseif ($nodeDepth > $currDepth) {
                $out .= Html::beginTag('ul', ['class' => '']) . "\n";
                $currDepth = $currDepth + ($nodeDepth - $currDepth);
            } elseif ($nodeDepth < $currDepth) {
                $out .= str_repeat("</li>\n</ul>", $currDepth - $nodeDepth) . "</li>\n";
                $currDepth = $currDepth - ($currDepth - $nodeDepth);
            }

            $nodeDepth = $node->depth;
            $nodeLeft = $node->lft;
            $nodeRight = $node->rgt;
            $nodeName = $node->name;

            $isChild = ($nodeRight == $nodeLeft + 1);

            $css = [];
            if (!$isChild) {
                $css[] = 'is-parent ';
            }
//            if ($node->isDisabled()) {
//                $css[] = 'kv-disabled ';
//            }
//            if (!$node->isActive()) {
//                $css[] = 'kv-inactive ';
//            }

            if (!empty($css)) {
                Html::addCssClass($nodeOptions, $css);
            }

            $out .= Html::beginTag('li', $nodeOptions) . "\n";
            $out .= IconDisplay::widget(['model' => $node]) . " ";
            $out .= Html::tag('span', $nodeName, ['class' => 'kv-node-label']) . "\n";
            ++$counter;

        }
        $out .= str_repeat("</li>\n</ul>", $nodeDepth) . "</li>\n";
        $out .= "</ul>\n";

        return $out;

    }
}
